<?php
	trait Trait_Flash {
		protected $flash__uniq_id;
		
		protected static $flash__uniq_ids		= null;
		protected static $flash__storage		= array();
		
		private function flash__init($uniq_id) {
			if(!isset(self::$flash__storage[ $uniq_id ])) {
				if(null === self::$flash__uniq_ids) {
					self::$flash__uniq_ids = array($uniq_id);
				} else {
					self::$flash__uniq_ids[] = $uniq_id;
				}
			}
			
			$this->flash__uniq_id = $uniq_id;
			
			return $this;
		}
		
		
		// hash key based on uniq_id
		protected function flash__hash_uniq_id($uniq_id) {
			$key = self::$flash__key_prefix . $uniq_id . self::$flash__key_suffix;
			
			if(self::$flash__key_hash) {
				$key = md5($key);
			}
			
			return $key;
		}
		
		// check on uniq_id storage
		protected function flash__lap() {
			// call this method in every custom getter method that doesn't use $this->flash__get(...)
			if((null === self::$flash__uniq_ids) || (count(self::$flash__uniq_ids) < 1)) {
				return $this;
			}
			
			self::$flash__uniq_ids = array_unique(self::$flash__uniq_ids);
			
			$uniq_id_hashes = array();
			
			foreach(self::$flash__uniq_ids as $_uniq_id) {
				$uniq_id_hashes[ $_uniq_id ] = self::flash__hash_uniq_id($_uniq_id);
			}
			
			$flash = $this;
			
			// api::cache() is basically a clone of the Memcached class
			$storage_values = api::cache()->get($uniq_id_hashes, function($_missing_hashes) use ($flash, $uniq_id_hashes) {
				// [_uniq_id => value, ]
				$missing_values = $flash->flash__process(array_keys($_missing_hashes));
				
				$return = array();
				
				foreach($missing_values as $uniq_id => $value) {
					$return[ $uniq_id_hashes[ $uniq_id ] ] = $value;
				}
				
				return $return;
			}, self::$flash__cache_expiry);
			
			if(count($storage_values)) {
				$hashes_uniq_id = array_flip($uniq_id_hashes);
				
				foreach($storage_values as $_hash => $value) {
					self::$flash__storage[ $hashes_uniq_id[ $_hash ] ] = $value;
				}
			}
			
			self::$flash__uniq_ids = null;
			
			return $this;
		}
		
		protected function flash__get($key) {
			// prime flash data where needed
			if((null !== self::$flash__uniq_ids) || (count(self::$flash__uniq_ids) > 0)) {
				self::flash__lap();
			}
			
			return (isset(self::$flash__storage[ $this->flash__uniq_id ][ $key ])?self::$flash__storage[ $this->flash__uniq_id ][ $key ]:null);
		}
		
		
		// public methods
		public function getId() {
			return $this->flash__uniq_id;
		}
	}