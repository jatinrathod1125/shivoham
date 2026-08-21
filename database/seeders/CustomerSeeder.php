<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Sarah Jenkins',
                'email' => 'sarah.jenkins@example.com',
                'phone' => '+1 (555) 234-5678',
                'status' => Customer::STATUS_ACTIVE,
                'total_orders_count' => 12,
                'total_spent' => 845.50,
                'address' => [
                    'type' => 'home',
                    'address_line1' => '742 Evergreen Terrace',
                    'city' => 'Springfield',
                    'state' => 'IL',
                    'postal_code' => '62704',
                    'country' => 'US',
                ]
            ],
            [
                'name' => 'Michael Chang',
                'email' => 'michael.chang@example.com',
                'phone' => '+1 (555) 345-6789',
                'status' => Customer::STATUS_ACTIVE,
                'total_orders_count' => 8,
                'total_spent' => 520.00,
                'address' => [
                    'type' => 'home',
                    'address_line1' => '1204 Pine Street, Apt 3B',
                    'city' => 'Seattle',
                    'state' => 'WA',
                    'postal_code' => '98101',
                    'country' => 'US',
                ]
            ],
            [
                'name' => 'Emma Watson',
                'email' => 'emma.watson@example.com',
                'phone' => '+1 (555) 456-7890',
                'status' => Customer::STATUS_ACTIVE,
                'total_orders_count' => 15,
                'total_spent' => 1230.75,
                'address' => [
                    'type' => 'home',
                    'address_line1' => '456 Oak Avenue',
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '78701',
                    'country' => 'US',
                ]
            ],
            [
                'name' => 'David Miller',
                'email' => 'david.miller@example.com',
                'phone' => '+1 (555) 567-8901',
                'status' => Customer::STATUS_ACTIVE,
                'total_orders_count' => 4,
                'total_spent' => 215.40,
                'address' => [
                    'type' => 'home',
                    'address_line1' => '89 Market Boulevard',
                    'city' => 'Denver',
                    'state' => 'CO',
                    'postal_code' => '80202',
                    'country' => 'US',
                ]
            ],
            [
                'name' => 'Olivia Rodriguez',
                'email' => 'olivia.rodriguez@example.com',
                'phone' => '+1 (555) 678-9012',
                'status' => Customer::STATUS_ACTIVE,
                'total_orders_count' => 19,
                'total_spent' => 1640.20,
                'address' => [
                    'type' => 'home',
                    'address_line1' => '321 Ocean Drive',
                    'city' => 'Miami',
                    'state' => 'FL',
                    'postal_code' => '33139',
                    'country' => 'US',
                ]
            ],
        ];

        foreach ($customers as $cData) {
            $addr = $cData['address'];
            unset($cData['address']);

            $customer = Customer::updateOrCreate(
                ['email' => $cData['email']],
                $cData
            );

            CustomerAddress::updateOrCreate(
                ['customer_id' => $customer->id, 'address_line1' => $addr['address_line1']],
                array_merge($addr, ['customer_id' => $customer->id, 'is_default' => true])
            );
        }
    }
}
