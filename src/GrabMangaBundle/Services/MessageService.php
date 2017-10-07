<?php

namespace GrabMangaBundle\Services;


class MessageService {
	
	public function formatErreurs($erreurs) {
		try {
			$messages = "";
			foreach ($erreurs as $erreur) {
				$messages.=$erreur->getMessage()." - ";
			}
			return substr($messages, 0, -3);
		} catch (\Exception $ex) {
			throw new \Exception("Erreur de formatage des erreurs.");
		}
		
	}
	
}