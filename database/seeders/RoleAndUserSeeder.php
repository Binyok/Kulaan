<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (UserRole::cases() as $roleEnum) {
            Role::firstOrCreate(['name' => $roleEnum->value]);
        }

        // ── ADMIN ─────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@kulaan.id'],
            [
                'name' => "Administrator Kula'an",
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'address' => 'Jl. Ahmad Yani Km 4.5, Banjarmasin',
            ]
        );
        $admin->assignRole(UserRole::ADMIN->value);

        // ── 3 UMKM ────────────────────────────────────────────────────────
        $umkmData = [
            [
                'email' => 'umkm.maju@gmail.com',
                'name' => 'Budi Santoso',
                'phone' => '082155511111',
                'address' => 'Jl. Kayu Tangi 1 No. 12, Banjarmasin Utara',
                'business_name' => 'Kedai Maju Jaya',
                'business_type' => 'Kuliner',
                'district' => 'Banjarmasin Utara',
                'latitude' => -3.298210,
                'longitude' => 114.588230,
            ],
            [
                'email' => 'umkm.nusantara@gmail.com',
                'name' => 'Siti Rahma',
                'phone' => '082155522222',
                'address' => 'Jl. Sutoyo S No. 45, Banjarmasin Barat',
                'business_name' => 'Dapur Nusantara',
                'business_type' => 'Kuliner',
                'district' => 'Banjarmasin Barat',
                'latitude' => -3.321110,
                'longitude' => 114.578900,
            ],
            [
                'email' => 'umkm.berkah@gmail.com',
                'name' => 'Andi Pratama',
                'phone' => '082155533333',
                'address' => 'Jl. Veteran No. 88, Banjarmasin Timur',
                'business_name' => 'Warung Berkah',
                'business_type' => 'Kuliner',
                'district' => 'Banjarmasin Timur',
                'latitude' => -3.319500,
                'longitude' => 114.602100,
            ],
        ];

        $createdUmkms = [];
        foreach ($umkmData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                ]
            );
            if (! $user->hasRole(UserRole::UMKM->value)) {
                $user->assignRole(UserRole::UMKM->value);
            }
            $umkm = Umkm::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $data['business_name'],
                    'business_type' => $data['business_type'],
                    'address' => $data['address'],
                    'district' => $data['district'],
                    'city' => 'Banjarmasin',
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]
            );
            $createdUmkms[] = $umkm;
        }

        // ── 3 DISTRIBUTOR ─────────────────────────────────────────────────
        $distributorData = [
            [
                'email' => 'distributor.borneo@gmail.com',
                'name' => 'PT Borneo Bahan Sembako',
                'phone' => '08119998881',
                'address' => 'Kawasan Industri Trisakti No. 8',
                'company_name' => 'PT Borneo Bahan Sembako',
                'district' => 'Banjarmasin Barat',
                'latitude' => -3.332000,
                'longitude' => 114.562000,
                'rating' => 4.85,
                'delivery_lead_time_days' => 1,
            ],
            [
                'email' => 'distributor.banua@gmail.com',
                'name' => 'CV Banua Makmur Jaya',
                'phone' => '08119998882',
                'address' => 'Jl. Lingkar Dalam Selatan No. 102',
                'company_name' => 'CV Banua Makmur Jaya',
                'district' => 'Banjarmasin Selatan',
                'latitude' => -3.345000,
                'longitude' => 114.610000,
                'rating' => 4.70,
                'delivery_lead_time_days' => 1,
            ],
            [
                'email' => 'distributor.kalimantan@gmail.com',
                'name' => 'UD Kalimantan Agro Pangan',
                'phone' => '08119998883',
                'address' => 'Jl. A. Yani Km 10 No. 45, Banjarbaru',
                'company_name' => 'UD Kalimantan Agro Pangan',
                'district' => 'Banjarmasin Tengah',
                'latitude' => -3.310000,
                'longitude' => 114.592000,
                'rating' => 4.60,
                'delivery_lead_time_days' => 2,
            ],
        ];

        $createdDistributors = [];
        foreach ($distributorData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                ]
            );
            if (! $user->hasRole(UserRole::DISTRIBUTOR->value)) {
                $user->assignRole(UserRole::DISTRIBUTOR->value);
            }
            $distributor = Distributor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $data['company_name'],
                    'address' => $data['address'],
                    'district' => $data['district'],
                    'city' => 'Banjarmasin',
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'rating' => $data['rating'],
                    'delivery_lead_time_days' => $data['delivery_lead_time_days'],
                    'is_active' => true,
                ]
            );
            $createdDistributors[] = $distributor;
        }

        // ── 3 KURIR ───────────────────────────────────────────────────────
        $kurirData = [
            [
                'email' => 'kurir.rizky@gmail.com',
                'name' => 'Rizky Kurir Express',
                'phone' => '085712341234',
                'address' => 'Jl. Pramuka No. 15, Banjarmasin',
                'vehicle_type' => 'Mobil Box',
                'vehicle_number' => 'DA 8123 AB',
                'lat' => -3.320000,
                'lng' => 114.590000,
                'distributor_idx' => 0,
            ],
            [
                'email' => 'kurir.arif@gmail.com',
                'name' => 'Arif Delivery Services',
                'phone' => '085712345678',
                'address' => 'Jl. Pahlawan No. 7, Banjarmasin',
                'vehicle_type' => 'Pickup',
                'vehicle_number' => 'DA 5567 CD',
                'lat' => -3.335000,
                'lng' => 114.575000,
                'distributor_idx' => 1,
            ],
            [
                'email' => 'kurir.fajar@gmail.com',
                'name' => 'Fajar Motor Cargo',
                'phone' => '085712347890',
                'address' => 'Jl. Veteran No. 22, Banjarmasin',
                'vehicle_type' => 'Motor',
                'vehicle_number' => 'DA 1122 EF',
                'lat' => -3.312000,
                'lng' => 114.598000,
                'distributor_idx' => 2,
            ],
        ];

        foreach ($kurirData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                ]
            );
            if (! $user->hasRole(UserRole::KURIR->value)) {
                $user->assignRole(UserRole::KURIR->value);
            }
            Courier::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'distributor_id' => $createdDistributors[$data['distributor_idx']]->id,
                    'vehicle_type' => $data['vehicle_type'],
                    'vehicle_number' => $data['vehicle_number'],
                    'is_available' => true,
                    'current_latitude' => $data['lat'],
                    'current_longitude' => $data['lng'],
                    'last_location_updated_at' => now(),
                ]
            );
        }
    }
}
