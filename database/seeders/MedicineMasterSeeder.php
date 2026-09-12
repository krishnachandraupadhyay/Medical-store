<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DosageForm;
use App\Models\Manufacturer;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MedicineMasterSeeder extends Seeder
{
    /**
     * Seed global pharmaceutical master records (store_id = null).
     */
    public function run(): void
    {
        // 1. Categories
        $categories = [
            ['name' => 'Analgesics & Antipyretics', 'description' => 'Pain relievers and fever reducers (Paracetamol, Ibuprofen, etc.)', 'sort_order' => 1],
            ['name' => 'Antibiotics & Anti-infectives', 'description' => 'Broad and narrow spectrum antibacterial medications', 'sort_order' => 2],
            ['name' => 'Antacids & Gastrointestinal', 'description' => 'Heartburn, reflux, and digestion medications (Pantoprazole, Omeprazole)', 'sort_order' => 3],
            ['name' => 'Antihistamines & Anti-allergic', 'description' => 'Allergy, rhinitis, and anti-itch medications (Cetirizine, Levocetirizine)', 'sort_order' => 4],
            ['name' => 'Cardiovascular & Antihypertensive', 'description' => 'Blood pressure and cardiac support medications (Amlodipine, Telmisartan)', 'sort_order' => 5],
            ['name' => 'Diabetes Management', 'description' => 'Oral hypoglycemic agents and insulins (Metformin, Glimepiride)', 'sort_order' => 6],
            ['name' => 'Respiratory & Cough/Cold', 'description' => 'Bronchodilators, cough syrups, and expectorants', 'sort_order' => 7],
            ['name' => 'Dermatological', 'description' => 'Topical anti-fungal, steroid, and antibacterial creams', 'sort_order' => 8],
            ['name' => 'Vitamins, Minerals & Supplements', 'description' => 'Multivitamins, Calcium, Vitamin D3, and dietary supplements', 'sort_order' => 9],
            ['name' => 'Central Nervous System (CNS)', 'description' => 'Neurological and psychiatric therapies', 'sort_order' => 10],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['store_id' => null, 'name' => $cat['name']],
                [
                    'slug' => Str::slug($cat['name']),
                    'description' => $cat['description'],
                    'status' => 'active',
                    'sort_order' => $cat['sort_order'],
                ]
            );
        }

        // 2. Manufacturers
        $manufacturers = [
            ['name' => 'Sun Pharmaceutical Industries', 'code' => 'SUN'],
            ['name' => 'Cipla Ltd', 'code' => 'CIPLA'],
            ['name' => "Dr. Reddy's Laboratories", 'code' => 'DRREDDY'],
            ['name' => 'Abbott India Ltd', 'code' => 'ABBOTT'],
            ['name' => 'Mankind Pharma Ltd', 'code' => 'MANKIND'],
            ['name' => 'Torrent Pharmaceuticals', 'code' => 'TORRENT'],
            ['name' => 'Alkem Laboratories Ltd', 'code' => 'ALKEM'],
            ['name' => 'Lupin Ltd', 'code' => 'LUPIN'],
            ['name' => 'Zydus Lifesciences', 'code' => 'ZYDUS'],
            ['name' => 'Glenmark Pharmaceuticals', 'code' => 'GLENMARK'],
        ];

        foreach ($manufacturers as $mfg) {
            Manufacturer::firstOrCreate(
                ['store_id' => null, 'name' => $mfg['name']],
                [
                    'code' => $mfg['code'],
                    'status' => 'active',
                ]
            );
        }

        // 3. Dosage Forms
        $dosageForms = [
            ['name' => 'Tablet', 'short_name' => 'Tab', 'sort_order' => 1],
            ['name' => 'Capsule', 'short_name' => 'Cap', 'sort_order' => 2],
            ['name' => 'Syrup', 'short_name' => 'Syr', 'sort_order' => 3],
            ['name' => 'Suspension', 'short_name' => 'Susp', 'sort_order' => 4],
            ['name' => 'Injection', 'short_name' => 'Inj', 'sort_order' => 5],
            ['name' => 'Ointment', 'short_name' => 'Oint', 'sort_order' => 6],
            ['name' => 'Cream', 'short_name' => 'Crm', 'sort_order' => 7],
            ['name' => 'Drops', 'short_name' => 'Drp', 'sort_order' => 8],
            ['name' => 'Gel', 'short_name' => 'Gel', 'sort_order' => 9],
            ['name' => 'Powder', 'short_name' => 'Pdr', 'sort_order' => 10],
            ['name' => 'Inhaler', 'short_name' => 'Inh', 'sort_order' => 11],
        ];

        foreach ($dosageForms as $df) {
            DosageForm::firstOrCreate(
                ['store_id' => null, 'name' => $df['name']],
                [
                    'short_name' => $df['short_name'],
                    'status' => 'active',
                    'sort_order' => $df['sort_order'],
                ]
            );
        }

        // 4. Units
        $units = [
            ['name' => 'Strip', 'short_name' => 'Strip', 'sort_order' => 1],
            ['name' => 'Bottle', 'short_name' => 'Btl', 'sort_order' => 2],
            ['name' => 'Box', 'short_name' => 'Box', 'sort_order' => 3],
            ['name' => 'Piece', 'short_name' => 'Pc', 'sort_order' => 4],
            ['name' => 'Tube', 'short_name' => 'Tube', 'sort_order' => 5],
            ['name' => 'Pack', 'short_name' => 'Pack', 'sort_order' => 6],
            ['name' => 'Vial', 'short_name' => 'Vial', 'sort_order' => 7],
            ['name' => 'Ampoule', 'short_name' => 'Amp', 'sort_order' => 8],
            ['name' => 'Sachet', 'short_name' => 'Sach', 'sort_order' => 9],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['store_id' => null, 'name' => $unit['name']],
                [
                    'short_name' => $unit['short_name'],
                    'status' => 'active',
                    'sort_order' => $unit['sort_order'],
                ]
            );
        }
    }
}
