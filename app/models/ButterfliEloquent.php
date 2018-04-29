<?php

/*
 * Our "Eloquent" Base Class, adds some handy things to Eloquent
 *
 */

class ButterfliEloquent extends Eloquent {
	protected $fields = null;
	protected $fieldErrors = null;
	protected $ingestedAttributes = [];

	public function Ingest($required = [], $index = null) {
		$fieldsToVerify = [];
		$this->ingestedAttributes = [];
		foreach($this->fields as $field) {
			$value = Input::get($field);
			if(!is_null($index)) {
				$value = $value[$index];
			}
			if(!is_null($value)) {
				$fieldsToVerify[$field] = $value;
				if($value != $this->$field) {
					$this->ingestedAttributes[$field] = $value;
				}
			}
		}

		Log::info($this->ingestedAttributes);

		$validator = Validator::make($fieldsToVerify, $required, $this->fieldErrors);
		if($validator->fails()) {
			Log::info("validator fails");
			$messages = $validator->messages();/////////
			Log::info($messages);
			$ret = "";
			foreach($messages->toArray() as $key => $message) {
				Log::info($message[0]);
				if(strlen($ret) > 0) {
					$ret .= "<br />";
				}

				$ret .= $message[0];
			}

			return $ret;
		}

		return null;
	}

	public function IngestedAttributes() {
		return $this->ingestedAttributes;
	}

	public function metadata($key, $value = NULL) {
		$metadata = json_decode($this->metadata_storage, true);
		if($value == NULL) {
			return $metadata[$key];
		}
		else {
			$metadata[$key] = $value;
			$this->metadata_storage = json_encode($metadata);
		}
	}

	public function getMetadata($key) {
		$metadata = json_decode($this->metadata_storage, true);
		return $metadata[$key];
	}
}