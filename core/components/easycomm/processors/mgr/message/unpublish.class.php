<?php

/**
 * Unpublish an ecMessage
 */
class easyCommMessageUnPublishProcessor extends modObjectProcessor {
	public $objectType = 'ecMessage';
	public $classKey = 'ecMessage';
	public $languageTopics = array('easycomm');
	//public $permission = 'delete';


	/**
	 * @return array|string
	 */
	public function process() {
		if (!$this->checkPermissions()) {
			return $this->failure($this->modx->lexicon('access_denied'));
		}

		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids)) {
			return $this->failure($this->modx->lexicon('ec_message_err_ns'));
		}

        $threadIds = array();
		foreach ($ids as $id) {
			/** @var ecMessage $object */
			if (!$object = $this->modx->getObject($this->classKey, $id)) {
				return $this->failure($this->modx->lexicon('ec_message_err_nf'));
			}
            $threadId = $object->get('thread');
            if(!array_key_exists($threadId, $threadIds)) {
                $threadIds[] = $threadId;
            }

            $object->set('published', 0);
            $object->set('publishedon', null);
            $object->set('publishedby', 0);

			$object->save();
		}

        $threads = $this->modx->getCollection('ecThread', array('id:IN' => $threadIds));
        /** @var ecThread $thread */
        foreach($threads as $thread){
            $thread->updateLastMessage();
        }

		return $this->success();
	}

}

return 'easyCommMessageUnPublishProcessor';