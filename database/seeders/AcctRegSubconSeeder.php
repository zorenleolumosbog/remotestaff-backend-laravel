<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcctRegSubconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subcons = [
            ['first_name' => 'Julie Ann', 'last_name' => 'Deniga', 'is_subcon' => '1', 'subcon_legacy_id' => '13363'],
            ['first_name' => 'Monica', 'last_name' => 'David', 'is_subcon' => '1', 'subcon_legacy_id' => '13209'],
            ['first_name' => 'Mylene', 'last_name' => 'Torres', 'is_subcon' => '1', 'subcon_legacy_id' => '12182'],
            ['first_name' => 'Joeld', 'last_name' => 'Singh', 'is_subcon' => '1', 'subcon_legacy_id' => '12832'],
            ['first_name' => 'Marck', 'last_name' => 'Chu', 'is_subcon' => '1', 'subcon_legacy_id' => '11223'],
            ['first_name' => 'Windel', 'last_name' => 'Tinzon', 'is_subcon' => '1', 'subcon_legacy_id' => '11985'],
            ['first_name' => 'Carissa', 'last_name' => 'San Luis', 'is_subcon' => '1', 'subcon_legacy_id' => '11867'],
            ['first_name' => 'Jet Aivin', 'last_name' => 'Festijo', 'is_subcon' => '1', 'subcon_legacy_id' => '13355'],
            ['first_name' => 'Les Mis', 'last_name' => 'Bustamante', 'is_subcon' => '1', 'subcon_legacy_id' => '12833'],
            ['first_name' => 'Monica', 'last_name' => 'Malanao', 'is_subcon' => '1', 'subcon_legacy_id' => '11219'],
            ['first_name' => 'Wilma', 'last_name' => 'Constantino', 'is_subcon' => '1', 'subcon_legacy_id' => '12182'],
            ['first_name' => 'Kyle', 'last_name' => 'Hoyohoy', 'is_subcon' => '1', 'subcon_legacy_id' => '7667'],
            ['first_name' => 'Russel', 'last_name' => 'Oledan', 'is_subcon' => '1', 'subcon_legacy_id' => '13312'],
            ['first_name' => 'Sheryl', 'last_name' => 'Ampat', 'is_subcon' => '1', 'subcon_legacy_id' => '12832'],
            ['first_name' => 'Alistair', 'last_name' => 'Delgado', 'is_subcon' => '1', 'subcon_legacy_id' => '11668'],
            ['first_name' => 'Angelino', 'last_name' => 'Vera', 'is_subcon' => '1', 'subcon_legacy_id' => '12747'],
            ['first_name' => 'Federico', 'last_name' => 'Gabriel', 'is_subcon' => '1', 'subcon_legacy_id' => '13062'],
            ['first_name' => 'Genesis', 'last_name' => 'Jumao-as', 'is_subcon' => '1', 'subcon_legacy_id' => '13312'],
            ['first_name' => 'Febbie', 'last_name' => 'Acula', 'is_subcon' => '1', 'subcon_legacy_id' => '9702'],
            ['first_name' => 'Noriel', 'last_name' => 'Francisco', 'is_subcon' => '1', 'subcon_legacy_id' => '12088'],
            ['first_name' => 'Philip Jan', 'last_name' => 'Baruis', 'is_subcon' => '1', 'subcon_legacy_id' => '12184'],
            ['first_name' => 'Danica Joyce', 'last_name' => 'Gapuz', 'is_subcon' => '1', 'subcon_legacy_id' => '13363'],
            ['first_name' => 'Jennifer', 'last_name' => 'Macalipay', 'is_subcon' => '1', 'subcon_legacy_id' => '12289'],
            ['first_name' => 'Jessa', 'last_name' => 'Herrera', 'is_subcon' => '1', 'subcon_legacy_id' => '11468'],
            ['first_name' => 'Katherine', 'last_name' => 'Tundag', 'is_subcon' => '1', 'subcon_legacy_id' => '12199'],
            ['first_name' => 'Zaida', 'last_name' => 'Royeras', 'is_subcon' => '1', 'subcon_legacy_id' => '11223'],
            ['first_name' => 'Cesar', 'last_name' => 'Sitchon', 'is_subcon' => '1', 'subcon_legacy_id' => '13259'],
            ['first_name' => 'Jerwin Albert', 'last_name' => 'Mendoza', 'is_subcon' => '1', 'subcon_legacy_id' => '11468'],
            ['first_name' => 'Jonassis', 'last_name' => 'Matias', 'is_subcon' => '1', 'subcon_legacy_id' => '13494'],
            ['first_name' => 'Laurence', 'last_name' => 'Cruz', 'is_subcon' => '1', 'subcon_legacy_id' => '12114'],
            ['first_name' => 'Deborah', 'last_name' => 'Rojano', 'is_subcon' => '1', 'subcon_legacy_id' => '13312'],
            ['first_name' => 'Jonalyn', 'last_name' => 'Calilung', 'is_subcon' => '1', 'subcon_legacy_id' => '13062'],
            ['first_name' => 'Rachel Leslie Anne', 'last_name' => 'Sayno', 'is_subcon' => '1', 'subcon_legacy_id' => '11668'],
            ['first_name' => 'Valerie Joy', 'last_name' => 'Argarin', 'is_subcon' => '1', 'subcon_legacy_id' => '12270'],
            ['first_name' => 'Barry', 'last_name' => 'Rodriguez', 'is_subcon' => '1', 'subcon_legacy_id' => '12949'],
            ['first_name' => 'Joan', 'last_name' => 'Aninipot', 'is_subcon' => '1', 'subcon_legacy_id' => '9002'],
            ['first_name' => 'Jubel', 'last_name' => 'Santos', 'is_subcon' => '1', 'subcon_legacy_id' => '12270'],
            ['first_name' => 'Jeffrey', 'last_name' => 'Duenas', 'is_subcon' => '1', 'subcon_legacy_id' => '12955'],
            ['first_name' => 'Mark Roger', 'last_name' => 'Bellocillo', 'is_subcon' => '1', 'subcon_legacy_id' => '11859'],
            ['first_name' => 'Romeo', 'last_name' => 'Laconsay', 'is_subcon' => '1', 'subcon_legacy_id' => '5866'],
            ['first_name' => 'Leznarth Mark', 'last_name' => 'Libay', 'is_subcon' => '1', 'subcon_legacy_id' => '12289'],
            ['first_name' => 'Maruel', 'last_name' => 'Viloria', 'is_subcon' => '1', 'subcon_legacy_id' => '12464'],
            ['first_name' => 'Michelle', 'last_name' => 'Cariaga', 'is_subcon' => '1', 'subcon_legacy_id' => '11223'],
            ['first_name' => 'Julie Ann', 'last_name' => 'Aralar', 'is_subcon' => '1', 'subcon_legacy_id' => '11296'],
            ['first_name' => 'Louie Bernadette', 'last_name' => 'Papera', 'is_subcon' => '1', 'subcon_legacy_id' => '12747'],
            ['first_name' => 'Mary Vianney', 'last_name' => 'Rojo', 'is_subcon' => '1', 'subcon_legacy_id' => '11974'],
            ['first_name' => 'Alzen', 'last_name' => 'Dominguez', 'is_subcon' => '1', 'subcon_legacy_id' => '12289'],
            ['first_name' => 'Chilton John', 'last_name' => 'Duat', 'is_subcon' => '1', 'subcon_legacy_id' => '7667'],
            ['first_name' => 'Ryan', 'last_name' => 'Paulino', 'is_subcon' => '1', 'subcon_legacy_id' => '13034'],
            ['first_name' => 'Ahne', 'last_name' => 'Lor', 'is_subcon' => '1', 'subcon_legacy_id' => '6988'],
            ['first_name' => 'John Cris', 'last_name' => 'Lasta', 'is_subcon' => '1', 'subcon_legacy_id' => '11864'],
            ['first_name' => 'Sandy', 'last_name' => 'Cariaga', 'is_subcon' => '1', 'subcon_legacy_id' => '5851'],
            ['first_name' => 'Kay-Ven', 'last_name' => 'Jao', 'is_subcon' => '1', 'subcon_legacy_id' => '13013'],
            ['first_name' => 'Mary Michelle', 'last_name' => 'Villanueva', 'is_subcon' => '1', 'subcon_legacy_id' => '12832'],
            ['first_name' => 'Vivian Grace', 'last_name' => 'Ordonez', 'is_subcon' => '1', 'subcon_legacy_id' => '13013'],
            ['first_name' => 'Nadine', 'last_name' => 'De Asis', 'is_subcon' => '1', 'subcon_legacy_id' => '12088'],
            ['first_name' => 'Rey Alvin', 'last_name' => 'Anapen', 'is_subcon' => '1', 'subcon_legacy_id' => '9003'],
            ['first_name' => 'Zipporah', 'last_name' => 'Angiwan-Cruz', 'is_subcon' => '1', 'subcon_legacy_id' => '12113'],
            ['first_name' => 'Gaudioso Lorenzo Ma Joseph', 'last_name' => 'Garcia', 'is_subcon' => '1', 'subcon_legacy_id' => '12289'],
            ['first_name' => 'Maynard Keynes', 'last_name' => 'Gumin', 'is_subcon' => '1', 'subcon_legacy_id' => '11859'],
            ['first_name' => 'Tobias Karl', 'last_name' => 'Seeger', 'is_subcon' => '1', 'subcon_legacy_id' => '9429'],
            ['first_name' => 'Ana Katrina', 'last_name' => 'Ansay', 'is_subcon' => '1', 'subcon_legacy_id' => '9002'],
            ['first_name' => 'Lady Marj', 'last_name' => 'Fernandez', 'is_subcon' => '1', 'subcon_legacy_id' => '13355'],
            ['first_name' => 'Leilani', 'last_name' => 'Visaya', 'is_subcon' => '1', 'subcon_legacy_id' => '12842'],
            ['first_name' => 'Justin', 'last_name' => 'Fernandez', 'is_subcon' => '1', 'subcon_legacy_id' => '12957'],
            ['first_name' => 'Marie Bless', 'last_name' => 'Navarro', 'is_subcon' => '1', 'subcon_legacy_id' => '13062'],
            ['first_name' => 'Methodius', 'last_name' => 'Caballes', 'is_subcon' => '1', 'subcon_legacy_id' => '12091'],
            ['first_name' => 'Pearl', 'last_name' => 'Ardonia', 'is_subcon' => '1', 'subcon_legacy_id' => '5866'],
        ];

        $actreg_id = 1;
        foreach ($subcons as $subcon) { 
            DB::connection('mysql')->table('tblm_b_onboard_actreg_basic')->insert([
               'reg_firstname'  => $subcon['first_name'],
               'reg_lastname'  => $subcon['last_name'],
               'isSubcon'  => $subcon['is_subcon']
            ]);

            DB::connection('mysql')->table('tblm_client_sub_contractor')->insert([
                'actreg_contractor_id'  => $actreg_id,
                'subcon_legacy_id'  => $subcon['subcon_legacy_id']
            ]);

            $actreg_id += 1;
        }
    }
}
