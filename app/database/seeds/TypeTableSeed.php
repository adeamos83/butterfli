<?php
class TypeTableSeed extends Seeder {

    public function run()
    {
        
        ProviderType::create(array('id' => 1,'name' => 'Limo', 'max_size'=>3, 'is_default' => 0,'is_visible'=>1,'created_at'=>'2016-07-23 01:23:09','updated_at'=>'2016-07-23 01:23:09','icon'=>'','price_per_unit_distance'=>'0.00','price_per_unit_time'=>'75.00','base_price'=>'75.00','base_distance'=>'5'));
		
		ProviderType::create(array('id' => 2,'name' => 'WheelChair', 'max_size'=>2, 'is_default' => 1,'is_visible'=>1,'created_at'=>'2016-07-23 01:23:09','updated_at'=>'2016-07-23 01:23:09','icon'=>'','price_per_unit_distance'=>'3.45','price_per_unit_time'=>'1.00','base_price'=>'32.20','base_distance'=>'1'));
		
		ProviderType::create(array('id' => 3,'name' => 'Ambulatory','max_size'=>2, 'is_default' => 0,'is_visible'=>1,'created_at'=>'2016-07-23 01:23:09','updated_at'=>'2016-07-23 01:23:09','icon'=>'','price_per_unit_distance'=>'1.90','price_per_unit_time'=>'1.00','base_price'=>'13.90','base_distance'=>'1'));
       
    }

}