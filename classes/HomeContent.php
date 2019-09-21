<?php
define('CONTENT_DIR', '/var/www/ricktoews.me/home-content');
class HomeContent {

	private $_dirs;

	public function __construct() {
		$this->_dirs = array_values(array_filter(scandir(CONTENT_DIR), function($item) { return $item !== '.' && $item !== '..'; }));
	}

    private function getTitle($contents) {
        $lines = explode('\n', $contents);
        $lastNdx = sizeof($lines);
        $ndx = 0;
        $title = '';
        while ($title === '' && $ndx < $lastNdx) {
          $result = preg_match('/<div class="title">(.*?)<\/div>/', $lines[$ndx], $matches);
          if ($result) {
             $title = strtolower(str_replace(' ', '-', $matches[1]));
          }
        }
        if ($title === '') { $title = 'no-title-found'; }
        return $title;
    }

	private function getMetaData($file, $contents) {
		$date = '';
		$seq = 0;
		preg_match('/^(\d{4}-\d{1,2}-\d{1,2})(-\d+)?\.html$/', $file, $matches);
		if ($matches) {
			$seq = isset($matches[2]) ? substr($matches[2], 1) : '0';
			$date = $matches[1];
		}

        $title = $this->getTitle($contents);
		return array('title' => $title, 'date' => $date, 'seq' => $seq);
	}

	private function getFiles($dir) {
		$files = array_filter(scandir($dir), function($item) { 
			$keep = (preg_match('/^(\d{4}-\d{1,2}-\d{1,2})(-\d+)?\.html$/', $item));
			return $keep;

		});
		return $files;
	}

	private function getContent($dir) {
		$content_dir = CONTENT_DIR . '/' . $dir;
		$files = $this->getFiles($content_dir);
		$content = [];
		foreach($files as $file) {
			$path = $content_dir . '/' . $file;
			$contents = file_get_contents($path);
			$metaData = $this->getMetaData($file, $contents);
			$content[] = array('title' => $metaData['title'], 'date' => $metaData['date'], 'seq' => $metaData['seq'], 'topic' => $dir, 'content' => $contents);
		}
		return array_values($content);
	}


	/*
		Expected to return an array of objects, each of which has a date, seq, topic, and content.
		Should be sorted by date and seq.
	*/
	public function get() {
		$contents = array();
		foreach ($this->_dirs as $dir) {
			if (!isset($contents[$dir])) { $contents[$dir] = array(); }
			$content = $this->getContent($dir);
			$contents = array_merge($contents, $content);
		}
		
		$contents = array_filter($contents, function($item) { return sizeof($item) > 0; });
		$contents = array_values($contents);
		usort($contents, function($a, $b) {
			$order = -1;
			if ($a['date'] < $b['date']) return -1*$order;
			else if ($a['date'] > $b['date']) return 1*$order;
			else if ($a['seq'] < $b['seq']) return -1*$order;
			else if ($a['seq'] > $b['seq']) return 1*$order;
			else if ($a['topic'] < $b['topic']) return -1*$order;
			else if ($a['topic'] > $b['topic']) return 1*$order;
		});
		return $contents;
	}

}
?>
