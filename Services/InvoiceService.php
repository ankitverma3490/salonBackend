<?php
/**
 * 🧾 Invoice Service - Handles Billing & Invoice Generation
 */
class InvoiceService
{
    private $db;
    private $notifier;

    public function __construct($db, $notifier)
    {
        $this->db = $db;
        $this->notifier = $notifier;
    }

    /**
     * Generate and deliver an invoice for a payment
     */
    public function generateAndDeliver($paymentId)
    {
        try {
            // 1. Fetch payment and related data
            $stmt = $this->db->prepare("
                SELECT p.*, s.name as salon_name, s.email as salon_email, s.address as salon_address
                FROM platform_payments p
                JOIN salons s ON p.salon_id = s.id
                WHERE p.id = ?
            ");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch();

            if (!$payment)
                throw new Exception("Payment record not found");

            // 2. Generate HTML Invoice
            $invoiceHtml = $this->renderInvoiceHtml($payment);

            // 3. Send email to Salon Owner
            $subject = "Invoice for Your Subscription - " . $payment['id'];
            $body = "Dear Salon Owner,<br><br>Please find your invoice for the recent payment below. Total: " . $payment['amount'] . " " . $payment['currency'] . ".<br><br>" . $invoiceHtml;

            // Use notifier to dispatch
            $ownerId = $this->getSalonOwnerId($payment['salon_id']);
            if ($ownerId) {
                $this->notifier->notifyUser($ownerId, "Invoice Generated", "Your invoice for payment #{$paymentId} is ready.", 'billing', null, true);

                // Specifically for billing, we can send the full invoice HTML (assuming notifier supports complex body)
                // Note: The sendEmailToUser method in notifier would need adjustment to handle this specific case or we send separately.
            }

            return true;
        } catch (Exception $e) {
            error_log("Invoice Error: " . $e->getMessage());
            return false;
        }
    }

    private function renderInvoiceHtml($payment)
    {
        $date = date('F d, Y', strtotime($payment['created_at']));
        return "
        <div style='max-width: 600px; margin: auto; padding: 30px; border: 1px solid #eee; font-size: 16px; line-height: 24px; font-family: \"Helvetica Neue\", \"Helvetica\", Helvetica, Arial, sans-serif; color: #555;'>
            <table cellpadding='0' cellspacing='0' style='width: 100%; line-height: inherit; text-align: left;'>
                <tr class='top'>
                    <td colspan='2' style='padding-bottom: 20px;'>
                        <table style='width: 100%; line-height: inherit; text-align: left;'>
                            <tr>
                                <td class='title' style='font-size: 45px; line-height: 45px; color: #333;'>
                                    SALON<span style='color: #3b82f6;'>PRO</span>
                                </td>
                                <td style='text-align: right;'>
                                    Invoice #: {$payment['id']}<br>
                                    Created: {$date}<br>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class='information'>
                    <td colspan='2' style='padding-bottom: 40px;'>
                        <table style='width: 100%; line-height: inherit; text-align: left;'>
                            <tr>
                                <td>
                                    Salon Network Admin<br>
                                    1234 Localhost St.<br>
                                    Node 8000, Local
                                </td>
                                <td style='text-align: right;'>
                                    {$payment['salon_name']}<br>
                                    {$payment['salon_email']}<br>
                                    {$payment['salon_address']}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class='heading' style='background: #fcfcfc; border-bottom: 1px solid #ddd; font-weight: bold;'>
                    <td style='padding: 5px; border-bottom: 1px solid #eee;'>Item</td>
                    <td style='padding: 5px; border-bottom: 1px solid #eee; text-align: right;'>Price</td>
                </tr>
                <tr class='item' style='border-bottom: 1px solid #eee;'>
                    <td style='padding: 5px; border-bottom: 1px solid #eee;'>Monthly Subscription Fee</td>
                    <td style='padding: 5px; border-bottom: 1px solid #eee; text-align: right;'>{$payment['amount']} {$payment['currency']}</td>
                </tr>
                <tr class='total' style='font-weight: bold;'>
                    <td></td>
                    <td style='padding: 5px; text-align: right; border-top: 2px solid #eee;'>Total: {$payment['amount']} {$payment['currency']}</td>
                </tr>
            </table>
        </div>
        ";
    }

    public function getId()
    {
        return "invoice_service";
    }

    /**
     * Send invoice for a specific booking to a customer
     */
    public function sendCustomerInvoice($bookingId)
    {
        try {
            // Fetch detailed booking info
            $stmt = $this->db->prepare("
                SELECT b.*, s.name as service_name, s.price, 
                       sal.name as salon_name, sal.email as salon_email, sal.address as salon_address, sal.phone as salon_phone,
                       sal.upi_id, sal.bank_details,
                       u.email as customer_email, p.full_name as customer_name, p.phone as customer_phone
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                JOIN salons sal ON b.salon_id = sal.id
                JOIN users u ON b.user_id = u.id
                LEFT JOIN profiles p ON u.id = p.user_id
                WHERE b.id = ?
            ");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();

            if (!$booking)
                throw new Exception("Booking not found");

            $invoiceHtml = $this->renderCustomerInvoiceHtml($booking);

            // Send email via specialized notifier method
            $subject = "Your Invoice from " . $booking['salon_name'];
            $this->notifier->sendInvoiceEmail($booking['user_id'], $subject, $invoiceHtml);

            return true;
        } catch (Exception $e) {
            error_log("Customer Invoice Error: " . $e->getMessage());
            return false;
        }
    }

    private function renderCustomerInvoiceHtml($data)
    {
        $date = date('M d, Y', strtotime($data['booking_date']));
        $invoiceNo = substr($data['id'], 0, 8);
        $amount = number_format($data['price'], 2);

        return "
        <div style='max-width: 800px; margin: 20px auto; padding: 40px; border: 1px solid #f0f0f0; border-radius: 20px; font-family: Arial, sans-serif; color: #444; background: #fff;'>
            <!-- Header Row -->
            <table style='width: 100%; margin-bottom: 40px;'>
                <tr>
                    <td style='vertical-align: top;'>
                        <div style='font-size: 40px; font-weight: 900; color: #3b82f6;'>
                            S<span style='margin-left: -5px; display: inline-block; transform: skewX(15deg); border-left: 3px solid #fff; padding-left: 2px;'>A</span>
                        </div>
                    </td>
                    <td style='text-align: right; vertical-align: top;'>
                        <h2 style='margin: 0 0 10px 0; font-size: 26px; font-weight: 900; color: #1a1a1a;'>Hair Salon Invoice</h2>
                        <div style='color: #888; font-size: 14px; font-weight: 500;'>
                            Invoice no: <span style='color: #1a1a1a;'>{$invoiceNo}</span><br>
                            Invoice date: <span style='color: #1a1a1a;'>{$date}</span><br>
                            Due: <span style='color: #1a1a1a; font-weight: 700;'>{$date}</span>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Address Row -->
            <table style='width: 100%; margin-bottom: 40px;'>
                <tr>
                    <td style='width: 33%; vertical-align: top;'>
                        <p style='margin: 0 0 5px 0; font-size: 10px; font-weight: 900; color: #aaa; text-transform: uppercase; letter-spacing: 1px;'>From</p>
                        <strong style='font-size: 16px; color: #1a1a1a;'>{$data['salon_name']}</strong><br>
                        <span style='font-size: 13px; color: #666;'>{$data['salon_email']}</span><br>
                        <span style='font-size: 13px; color: #888;'>{$data['salon_address']}</span>
                    </td>
                    <td style='width: 33%; vertical-align: top;'>
                        <p style='margin: 0 0 5px 0; font-size: 10px; font-weight: 900; color: #aaa; text-transform: uppercase; letter-spacing: 1px;'>Bill to</p>
                        <strong style='font-size: 16px; color: #1a1a1a;'>{$data['customer_name']}</strong><br>
                        <span style='font-size: 13px; color: #666;'>{$data['customer_phone']}</span>
                    </td>
                    <td style='width: 33%; vertical-align: top;'>
                        <p style='margin: 0 0 5px 0; font-size: 10px; font-weight: 900; color: #aaa; text-transform: uppercase; letter-spacing: 1px;'>Ship to</p>
                        <span style='font-size: 13px; color: #888;'>Same as billing address</span>
                    </td>
                </tr>
            </table>

            <!-- Table Section -->
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 40px; border: 1px solid #f5f5f5; border-radius: 10px; overflow: hidden;'>
                <thead>
                    <tr style='background: #2563eb; color: #fff; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;'>
                        <th style='padding: 15px; text-align: left;'>Description</th>
                        <th style='padding: 15px; text-align: center;'>Rate</th>
                        <th style='padding: 15px; text-align: center;'>Qty</th>
                        <th style='padding: 15px; text-align: center;'>Tax</th>
                        <th style='padding: 15px; text-align: center;'>Disc</th>
                        <th style='padding: 15px; text-align: right;'>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style='font-size: 14px; border-bottom: 1px solid #f9f9f9;'>
                        <td style='padding: 20px 15px;'>
                            <strong style='display: block; color: #1a1a1a;'>{$data['service_name']}</strong>
                            <span style='font-size: 11px; color: #999;'>Professional salon treatment</span>
                        </td>
                        <td style='padding: 20px 15px; text-align: center; color: #666;'>RM {$data['price']}</td>
                        <td style='padding: 20px 15px; text-align: center; color: #666;'>1</td>
                        <td style='padding: 20px 15px; text-align: center; color: #666;'>0%</td>
                        <td style='padding: 20px 15px; text-align: center; color: #666;'>0%</td>
                        <td style='padding: 20px 15px; text-align: right; font-weight: 700; color: #1a1a1a;'>RM {$amount}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Footer Stats -->
            <table style='width: 100%;'>
                <tr>
                    <td style='width: 60%; vertical-align: top;'>
                        <p style='margin: 0 0 5px 0; font-weight: 900; color: #1a1a1a;'>Payment instruction</p>
                        <p style='margin: 0; font-size: 13px; color: #666;'>
                            UPI: " . ($data['upi_id'] ?: 'pay@example.com') . "<br>
                            Bank: " . ($data['bank_details'] ?: 'Maybank 5642XXX') . "
                        </p>
                        
                        <p style='margin: 20px 0 5px 0; font-weight: 900; color: #1a1a1a;'>Notes</p>
                        <p style='margin: 0; font-size: 13px; color: #888; font-style: italic;'>Thank you for your visit!</p>
                    </td>
                    <td style='width: 40%; vertical-align: top;'>
                        <table style='width: 100%; font-size: 14px; border-spacing: 0 8px;'>
                            <tr>
                                <td style='color: #666;'>Subtotal</td>
                                <td style='text-align: right; font-weight: 700;'>RM {$amount}</td>
                            </tr>
                            <tr>
                                <td style='color: #666;'>Discount (0%)</td>
                                <td style='text-align: right; font-weight: 700;'>RM 0.00</td>
                            </tr>
                            <tr>
                                <td style='color: #666;'>Sales Tax</td>
                                <td style='text-align: right; font-weight: 700;'>RM 0.00</td>
                            </tr>
                            <tr>
                                <td colspan='2' style='border-top: 1px solid #eee; padding-top: 10px;'></td>
                            </tr>
                            <tr style='font-size: 18px; font-weight: 900;'>
                                <td style='color: #1a1a1a;'>Total</td>
                                <td style='text-align: right; color: #1a1a1a;'>RM {$amount}</td>
                            </tr>
                            <tr style='color: #059669; font-weight: 700;'>
                                <td>Amount paid</td>
                                <td style='text-align: right;'>- RM {$amount}</td>
                            </tr>
                            <tr>
                                <td colspan='2' style='padding-top: 15px;'>
                                    <div style='background: #eff6ff; padding: 15px; border-radius: 12px; color: #1d4ed8;'>
                                        <table style='width: 100%;'>
                                            <tr>
                                                <td style='font-weight: 700;'>Balance Due</td>
                                                <td style='text-align: right; font-size: 20px; font-weight: 900;'>RM 0.00</td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        ";
    }

    public function getSalonOwnerId($salonId)
    {
        $stmt = $this->db->prepare("SELECT user_id FROM user_roles WHERE salon_id = ? AND role = 'owner' LIMIT 1");
        $stmt->execute([$salonId]);
        $row = $stmt->fetch();
        return $row ? $row['user_id'] : null;
    }
}
