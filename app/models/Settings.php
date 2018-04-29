<?php

class Settings extends Eloquent {

    protected $table = 'settings';

	 /*  value

	    Look up a setting and return its value

	    $key -      name of the setting
	*/

	public static function value($key) {
	    $settings = Settings::where('key', $key)->first();
	    return $settings->value;
	}
}