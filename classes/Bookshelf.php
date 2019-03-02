<?php
class Bookshelf {
    private $_shelf;
	private $_api_key;
	private $_api_secret;

    public function __construct($shelf = 'professional-development')
    {
        $this->_shelf = $shelf;
		$this->_api_key = 'n8hATXd6wzepLEZo5zlhkg';
		$this->_api_secret = 'Tska2BISgXaryDkX4Y3J2eBe5QHEd45vOPsYqH6I';
		$this->_user_id = '4175880-retorick';
    }

    public function old_get_books()
    {
        $booklist = $this->_parse_book_data();
        usort($booklist, 'Bookshelf::_bookcmp');
        return $booklist;
    }

	private function _xml_to_json($xml) {
		$p = xml_parser_create();
		xml_parse_into_struct($p, $xml, $vals, $index);
		xml_parser_free($p);
		return $vals;
	}

	private static function _fix_desc($str) {
		return $str;
	}

	private function _goodreads_get_user_books() {
		$url = 'https://www.goodreads.com/review/list?sort=position&v=2&id=' . $this->_user_id . '&key=' . $this->_api_key . '&shelf=' . $this->_shelf;
		$xml = file_get_contents($url);
		return $xml;
	}

	private function _process_raw_json($raw) {
		$raw_list = array_filter($raw, function($v) { return $v['level'] >= 4; });
		$books = array();
		$book = array();
		$ndx = 0;
		foreach($raw_list as $item) {
			$tag = $item['tag'];
			$level = $item['level'];
			$value = isset($item['value']) ? $item['value'] : '';
			if ($level === 4) {
				if ($tag === 'BODY') {
					$book['review'] = $value;
				}
			} else if ($level === 5) {
				if ($tag === 'ID') {
					if (isset($book['title'])) {
						$book['ndx'] = $ndx++;
						array_push($books, $book);
					}
					$book = array();
				} else if ($tag === 'TITLE') {
					$book['title'] = $value;
				} else if ($tag === 'IMAGE_URL') {
					$book['image'] = $value;
				} else if ($tag === 'DESCRIPTION') {
					$book['description'] = self::_fix_desc($value);
				} else if ($tag === 'PUBLISHER') {
					$book['publisher'] = $value;
				} else if ($tag === 'PUBLICATION_YEAR') {
					$book['year'] = $value;
				} else if ($tag === 'NUM_PAGES') {
					$book['pages'] = $value;
				}
				
			} else if ($level === 7) {
 				if ($tag === 'NAME') {
					if (!isset($book['authors'])) {
						$book['authors'] = array();
					}
					array_push($book['authors'], $value);
				}
			}
		}
		if (isset($book['title'])) {
			$book['ndx'] = $ndx++;
			array_push($books, $book);
		}

		return $books;
   	}

	public function get_books() {
		$xml = self::_goodreads_get_user_books();
		$vals = self::_xml_to_json($xml);
		$payload = self::_process_raw_json($vals);
		return $payload;
	}

	public function get_shelves() {
        try {
            $url = 'https://www.goodreads.com/shelf/list.xml?user_id=' . $this->_user_id . '&key=' . $this->_api_key;
            $rss = file_get_contents($url);
            $p = xml_parser_create();
		    xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
            xml_parse_into_struct($p, $rss, $vals, $index);
			xml_parser_free($p);
        }
        catch (Exception $e) {
        }
        return array_filter($vals, function($v) { return $v['level'] === 4 && $v['tag'] === 'NAME'; });
	}

    private static function _bookcmp($a, $b) {
        if ($a['sort_val'] == $b['sort_val']) {
            $result = 0;
        }
        else {
            $result = ($a['sort_val'] < $b['sort_val']) ? -1 : 1;
        }
        return $result;
    }

    private function _get_book_feed() {
        try {
            $url = 'http://www.goodreads.com/review/list_rss/4175880-retorick?key=78190c41cfafe61b23163e7c15f39669dcf0ae14&shelf=' . $this->_shelf;
			$url = 'https://www.goodreads.com/review/list?v=2&id=' . $this->_user_id . '&key=' . $this->_api_key . '&shelf=' . $this->_shelf;
error_log('Bookshelf _get_book_feed ' . $url);
            $rss = file_get_contents($url);
            $p = xml_parser_create();
            xml_parse_into_struct($p, $rss, $vals, $index);
        }
        catch (Exception $e) {
        }
        return $vals;
    }


    private function _process_article($str) {
        $processedStr = $str;

        if (substr($str, 0, 4) == 'The ') {
            $processedStr = substr($str, 4) . ', The';
        }
        elseif (substr($str, 0, 3) == 'An ') {
            $processedStr = substr($str, 3) . ', An';
        }
        elseif (substr($str, 0, 2) == 'A ') {
            $processedStr = substr($str, 2) . ', A';
        }
        return $processedStr;
    }


    private function _intro($str, $ndx) {
        $minlength = 0;
        $maxlength = 200;

        $intro = substr($str, 0, $maxlength); 
        $chr = substr($intro, strlen($intro) - 1, 1);
        while (strlen($intro) >= $minlength && preg_match('/\w/', $chr)) {
            $intro = substr($intro, 0, strlen($intro) - 1);
            $chr = substr($intro, strlen($intro) - 1, 1);
        }
        if (strlen($intro) < strlen($str)) {
            return $intro . ' <span id="more_'.$ndx.'" class="more">[more...]</span>';
        }
        else {
            return $intro;
        }
    }


    private function _parse_book_data() {
        $data = $this->_get_book_feed();
error_log('Bookshelf _parse_book_data ' . json_encode($data));
        $booklist = array();
        $ndx = 0;
        foreach ($data as $v) {
            if ($v['level'] == 2) {
                $ndx_increment = 1;
            }
            if ($v['level'] == 3) {
                $bookdata = $v['tag'] == 'ITEM';
            }
            if ($v['level'] == 4 && $bookdata) { 
                $tag = $v['tag'];
                $value = isset($v['value']) ? trim($v['value']) : '';
                switch ($tag) {
                    case 'TITLE':
                        $booklist[$ndx]['sort_val'] = $this->_process_article($value);
                        $booklist[$ndx]['title'] = $value;
                        break;
                    case 'BOOK_IMAGE_URL':
                        $booklist[$ndx]['image'] = $value;
                        break;
                    case 'AUTHOR_NAME':
                        $booklist[$ndx]['author'] = $value;
                        break;
                    case 'USER_REVIEW':
                        $booklist[$ndx]['thoughts_intro'] = $this->_intro($value, $ndx);
                        $booklist[$ndx]['thoughts'] = $value;
                        $booklist[$ndx]['ndx'] = $ndx;
                        break;
                    case 'BOOK_DESCRIPTION':
                        $booklist[$ndx]['description'] = $value;
                        break;
                    case 'ISBN':
                        $booklist[$ndx]['isbn'] = $value;
                        break;
                }
                $ndx += $ndx_increment;
                $ndx_increment = 0;
            }
        }
        return $booklist;
    }

}
?>
