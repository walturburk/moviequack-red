<?php

class Template { //Class for processing html-template

  protected $file;
  protected $values = array();
  protected $ispreprocessed = false;

  public function __construct($file) {
		$this->sourceHtmlFile($file);
	}

	public function sourceHtmlFile($file) {
		$this->file = $file;
		$html = file_get_contents($file);
		$this->html = $html;
	}

	public function sourceHtml($html) {
		$this->html = $html;
	}

	public function preProcessHtml($html) {

    $this->ispreprocessed = true;

		$htmlarray = explode("<", $html);
		$identifier = 0;

		foreach($htmlarray AS $key => $tag) {
			//$htmlarray[$key] = "<".$tag;
			if (strpos($tag, "/") === 0) {
				$identifier ++;
				//echo "KEY: ".$key." TAG: ".$tag." strpos: ".strpos($tag, "/")."<br>";
				$closingtagkey = $key;
				$closingtag = $tag;
				$searchkey = $key;

				$tagname2 = explode("/", $closingtag);
				$tagname3 = $tagname = explode(">", $tagname2[1]);
				$tagname4 = $tagname = explode(" ", $tagname3[0]);
				$tagname = $tagname4[0];

				while (strpos($htmlarray[$searchkey], "/") === 0 || strpos($htmlarray[$searchkey], "IDENTIFIERARE") > 0 || strpos($htmlarray[$searchkey], $tagname) === false) {
					//echo preg_match("/".$tagname."(?![0-9])+/s", $htmlarray[$searchkey])."<br>";

					$searchkey --;
					//echo $htmlarray[$searchkey]." S: ".strpos($htmlarray[$searchkey], $tagname)."<br>";
					//echo "ASDKEY: ".$tagname." TAG: ".$htmlarray[$searchkey]." strpos: ".strpos($htmlarray[$searchkey], $tagname)."<br>";
				}
				//echo "OPENINGTAG: ".$htmlarray[$searchkey]." CLOSINGTAG: ".$closingtag." STRPOS: ".strpos($htmlarray[$searchkey], $tagname)."<br>";
				//echo "STARTINGTAG: ".$htmlarray[$searchkey]."<br>";
				$idstamp = $tagname."IDENTIFIERARE".$identifier."";



				$htmlarray[$searchkey] = preg_replace('/'.$tagname.'/', $idstamp, $htmlarray[$searchkey], 1);//str_replace($tagname, $idstamp, $htmlarray[$searchkey]);
				$htmlarray[$closingtagkey] = preg_replace('/'.$tagname.'/', $idstamp, $htmlarray[$closingtagkey], 1);//str_replace($tagname, $idstamp, $htmlarray[$closingtagkey]);
			}
		}

		$html = implode("<", $htmlarray);


		return $html;
	}

	public function selectElement($identifier) {
    if (!$this->ispreprocessed) {
      $this->html = $this->preProcessHtml($this->html);
    }
		$regex = $this->chooseSelector($identifier);
		preg_match($regex, $this->html, $match);
		return $match[0];
	}

	public function removeElement($identifier) {
    if (!$this->ispreprocessed) {
      $this->html = $this->preProcessHtml($this->html);
    }
		$regex = $this->chooseSelector($identifier);
		$this->html = preg_replace($regex, "", $this->html);
	}

	public function postProcessHtml($html) {
		$regex = '/IDENTIFIERARE[0-9]+/s';
		$output = preg_replace($regex, "", $html);
		return $output;
	}

	public function html($selector) {
    if (!$this->ispreprocessed) {
      $this->html = $this->preProcessHtml($this->html);
    }
		$html = $this->selectElement($selector);
		return $this->postProcessHtml($html);
	}

	public function text($selector) {
    if (!$this->ispreprocessed) {
      $this->html = $this->preProcessHtml($this->html);
    }
		$html = $this->selectElement($selector);
		return $this->postProcessHtml($html);
	}

	public function chooseSelector($selectinput) {
    if (!$this->ispreprocessed) {
      $this->html = $this->preProcessHtml($this->html);
    }
		$elementname = "[A-Z][A-Z0-9]*";
		$anychar = '[=a-z0-9\\ \'_{}"-]*?';
		$firstclasses = '([=a-z0-9\\ \'_{}"-] )*?';
		$lastclasses = '( [=a-z0-9\\ \'_{}"-])*?';

		if (strpos($selectinput, ".") > -1) {

			$attrname = "class";

			$selectinput = explode(".", $selectinput);
			//$output = "asd".$selectinput[0]."DEL ".$selectinput[1];
			if ($selectinput[0] == "") {
				$element = $elementname;
			} else {
				$element = $selectinput[0];
			}

			$regex = '/<('.$element.'IDENTIFIERARE[0-9]+)('.$anychar.") ".$attrname.'=(["\'])'.$anychar.$selectinput[1].$anychar.'\3('.$anychar.')>.*<\/\1>/si';

		} else if (strpos($selectinput, "#") > -1) {

			$attrname = "id";

			$selectinput = explode("#", $selectinput);
			//$output = "asd".$selectinput[0]."DEL ".$selectinput[1];
			if ($selectinput[0] == "") {
				$element = $elementname;
			} else {
				$element = $selectinput[0];
			}
			$regex = '/<('.$element.'IDENTIFIERARE[0-9]+)('.$anychar.") ".$attrname.'=(["\'])'.$anychar.$selectinput[1].$anychar.'\3('.$anychar.')>.*<\/\1>/si';

		} else if (strpos($selectinput, "[") > -1) {



			$selectinput2 = explode("[", $selectinput);
			$selectinput2[1] = str_replace("]", "", $selectinput2[1]);
			$selectinput = explode("=", $selectinput2[1]);

			if ($selectinput2[0] == "") {
				$element = $elementname;
				$attrname = $selectinput[0];
			} else {
				$attrname = $selectinput[0];
				$element = $selectinput2[0];
			}
			$selectinput[1] = str_replace('"', "", $selectinput[1]);
			$selectinput[1] = str_replace("'", "", $selectinput[1]);
			$regex = '/<('.$element.'IDENTIFIERARE[0-9]+)('.$anychar.") ".$attrname.'=(["\'])'.$anychar.$selectinput[1].$anychar.'\3('.$anychar.')>.*<\/\1>/si';

		} else {

			$regex = '/<('.$selectinput.'IDENTIFIERARE[0-9]+)([=a-z0-9\\ \'_{}"-]*?)>.*<\/\1>/si';

		}


		return $regex;
	}

  public function set($key, $value) {
    $this->values[$key] = $value;
  }

  public function getVariables($filecontent = null) {
    if ($filecontent == null) {
      $filecontent = $this->html;
    }
    $pattern = "/{(.*?)}/";
    preg_match_all($pattern, $filecontent, $matches);
    return $matches;
  }

  public function setVariables($matches) {
    foreach ($matches[0] AS $match) {
      $match = str_replace("{", "", $match);
      $match = str_replace("}", "", $match);
      global $$match;
      if (isset($$match) && !isset($this->values[$match]) && ($this->values[$match] !== "")) {
        $this->set($match, $$match);
      }
    }
  }

  public function flushVariables($matches) {
    foreach ($matches[0] AS $match) {
      $match = str_replace("{", "", $match);
      $match = str_replace("}", "", $match);
      global $$match;
        $this->set($match, null);
    }
  }

  public function grabElement($identifier) {
    if (!$this->ispreprocessed) {
      $this->html = $this->preProcessHtml($this->html);
    }
    $element = $this->selectElement($identifier);
    $this->removeElement($identifier);
    return $element;
  }


  public function output($output = null) {
    if ($output == null) {
      $output = $this->html;
    }
    $variables = $this->getVariables($output);
    $this->setVariables($variables);

    foreach ($this->values as $key => $value) {
      $tagToReplace = "{{$key}}";
      $output = str_replace($tagToReplace, $value, $output);
    }

    if ($this->ispreprocessed) {
      $output = $this->postProcessHtml($output);
    }
     $this->flushVariables($variables);
    return $output;
  }
}

?>
