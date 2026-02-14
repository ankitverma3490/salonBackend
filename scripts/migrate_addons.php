<?php
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Auth.php';

$db = Database::getInstance()->getConnection();

$addOns = [
    [
        'id' => Auth::generateUuid(),
        'name' => 'Advanced Analytics',
        'slug' => 'advanced-analytics',
        'description' => 'Detailed business insights and performance metrics',
        'price_monthly' => 499.00,
        'icon' => 'BarChart3'
    ],
    [
        'id' => Auth::generateUuid(),
        'name' => 'WhatsApp Integration',
        'slug' => 'whatsapp-integration',
        'description' => 'Send booking confirmations and reminders via WhatsApp',
        'price_monthly' => 299.00,
        'icon' => 'Smartphone'
    ],
    [
        'id' => Auth::generateUuid(),
        'name' => 'Website Integration',
        'slug' => 'website-integration',
        'description' => 'Embed booking widget on your salon website',
        'price_monthly' => 799.00,
        'icon' => 'Globe'
    ],
    [
        'id' => Auth::generateUuid(),
        'name' => 'Dedicated Support',
        'slug' => 'dedicated-support',
        'description' => 'Priority support with dedicated account manager',
        'price_monthly' => 1999.00,
        'icon' => 'Headphones'
    ]
];

foreach ($addOns as $addon) {
    $stmt = $db->prepare("INSERT IGNORE INTO subscription_addons (id, name, slug, description, price_monthly, icon) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $addon['id'],
        $addon['name'],
        $addon['slug'],
        $addon['description'],
        $addon['price_monthly'],
        $addon['icon']
    ]);
}

echo "Add-ons migrated successfully.\n";
