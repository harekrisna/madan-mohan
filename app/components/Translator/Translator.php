<?php

class Translator implements Nette\Localization\ITranslator {
    /** @var array */
    protected $translations = array();

    /**
     * @param string $lang
     * @param array $translations
     */
    public function __construct($lang = 'en', array $translations = array())
    {
        $translations = $translations + $this->getTranslationsFromFile($lang);
        $this->translations = $translations;
    }

    /**
     * Sets language of translation.
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->translations = $this->getTranslationsFromFile($lang);
    }

    /**
     * @param string $lang
     * @throws \Exception
     * @return array
     */
    protected function getTranslationsFromFile($lang)
    {
        if (!$translations = @include (__DIR__ . "/$lang.php")) {
            throw new \Exception("Translations for language '$lang' not found.");
        }

        return $translations;
    }

    /************************* interface \Nette\Localization\ITranslator **************************/

    /**
     * @param string $message
     * @param int $count plural
     * @return string
     */
    public function translate($message, $count = NULL, $decode = FALSE)
    {
        if(isset($this->translations[$message])) {
	        return $decode == TRUE
            	? html_entity_decode($this->translations[$message])
				: $this->translations[$message];

        	return $this->translations[$message];
        }
		else {
		    return $message;
		}
    }
}