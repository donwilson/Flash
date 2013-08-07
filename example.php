<?php
	require_once(__DIR__ ."/abstract.php");
	require_once(__DIR__ ."/trait.php");
	
	class Entity extends Abstract_Flash {
		use Trait_Flash;
		
		// Flash settings
		private static $flash__cache_expiry	= 86400;   // integer, in seconds
		private static $flash__key_hash		= true;   // boolean, run md5() on cache key
		private static $flash__key_prefix	= "entity:by_id:";   // string, cache key prefix
		private static $flash__key_suffix	= "";   // string, cache key suffix
		
		
		public function __construct($entity_id) {
			return $this->flash__init($entity_id);
		}
		
		protected function flash__process($entity_ids) {
			// api::db() is a simple database class, 'id' sets array keys to each respective `entity`.`id`
			return api::db()->get_results("
				SELECT *
				FROM `entity`
				WHERE
					`id` IN ('". implode("', '", $entity_ids) ."')
			", 'id');
		}
		
		
		// Data
		
		public function getSlug() {
			return $this->flash__get('seoid');
		}
		
		public function getTitle() {
			return $this->flash__get('title');
		}
		
		public function getTypeId() {
			return $this->flash__get('type_id');
		}
		
		public function getPrimaryMediaId() {
			return $this->flash__get('media_id');
		}
		
		public function getDateCreated() {
			return $this->flash__get('date_created');
		}
		
		public function getDateUpdated() {
			return $this->flash__get('date_created');
		}
		
		
		// Dynamic methods
		
		public function getUrl($section="index") {
			return "/entity/". $this->getId() ."/". ((!empty($section) && ("index" !== $section))?$section .".html":"");
		}
		
		
		// Other methods
		
		public function getType() {
			return api::entity_type( $this->getTypeId() );
		}
		
		public function getPrimaryMedia() {
			return api::media( $this->getPrimaryMediaId() );
		}
	}