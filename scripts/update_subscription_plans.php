<?php
require_once dirname(__DIR__) . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Disable foreign key checks temporarily to allow clean slate
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 1. Delete existing plans
    $db->exec("DELETE FROM subscription_plans");

    // 2. Insert the new plans
    $plans = [
        [
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Perfect for small salons getting started',
            'price_monthly' => 999.00,
            'price_yearly' => 9990.00,
            'max_staff' => 5,
            'max_services' => 20,
            'features' => json_encode([
                "Up to 100 bookings/month",
                "Basic appointment management",
                "Customer database",
                "SMS notifications",
                "Mobile app access",
                "Basic reporting",
                "Email support"
            ]),
            'is_featured' => 0,
            'sort_order' => 1
        ],
        [
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'Most popular choice for growing salons',
            'price_monthly' => 2499.00,
            'price_yearly' => 24990.00,
            'max_staff' => 20,
            'max_services' => 100,
            'features' => json_encode([
                "Unlimited bookings",
                "Advanced appointment management",
                "Customer loyalty programs",
                "SMS + Email + WhatsApp notifications",
                "Staff management",
                "Inventory tracking",
                "Advanced analytics",
                "Online payments",
                "Custom branding",
                "Priority support"
            ]),
            'is_featured' => 1,
            'sort_order' => 2
        ],
        [
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'description' => 'Complete solution for salon chains',
            'price_monthly' => 4999.00,
            'price_yearly' => 49990.00,
            'max_staff' => 999,
            'max_services' => 999,
            'features' => json_encode([
                "Everything in Professional",
                "Unlimited salon locations",
                "Multi-location management",
                "Advanced staff scheduling",
                "Franchise management",
                "Custom integrations",
                "White-label solution",
                "Dedicated account manager",
                "24/7 phone support",
                "Custom training"
            ]),
            'is_featured' => 0,
            'sort_order' => 3
        ]
    ];

    $stmt = $db->prepare("
        INSERT INTO subscription_plans (id, name, slug, description, price_monthly, price_yearly, max_staff, max_services, features, is_featured, sort_order)
        VALUES (UUID(), :name, :slug, :description, :price_monthly, :price_yearly, :max_staff, :max_services, :features, :is_featured, :sort_order)
    ");

    $newPlanIds = [];
    foreach ($plans as $plan) {
        $stmt->execute($plan);
        $newPlanIds[$plan['slug']] = $db->query("SELECT id FROM subscription_plans WHERE slug = '{$plan['slug']}'")->fetchColumn();
        echo "Inserted plan: " . $plan['name'] . " (ID: " . $newPlanIds[$plan['slug']] . ")\n";
    }

    // 3. Update all salons to use the new Starter plan as default
    if (isset($newPlanIds['starter'])) {
        $starterId = $newPlanIds['starter'];
        $db->exec("UPDATE salons SET subscription_plan_id = '{$starterId}'");
        echo "Updated all salons to use the Starter plan.\n";
    }

    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Successfully updated subscription plans to match the new pricing model.\n";

} catch (Exception $e) {
    // Ensure foreign key checks are re-enabled even on error
    if (isset($db)) {
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
    echo "Error: " . $e->getMessage() . "\n";
}
