<?php
class PTN{
    public function _escape_regex($string){
        return preg_replace('/[\-\[\]{}()*+?.,\\\^$|#\s]/', '\\$&', $string);
    }

    /*
    For retro-compatibility on php version < 8.0
    From https://stackoverflow.com/a/834355
    */
    public function str_starts_with($haystack, $needle){
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }

    public function __construct(){
        $this->torrent = null;
        $this->excess_raw = null;
        $this->group_raw = null;
        $this->start = null;
        $this->end = null;
        $this->title_raw = null;
        $this->parts = null;

        $this->patterns = [
            'season' => '(s?([0-9]{1,2}))[ex]',
            'episode' => '([ex]([0-9]{2})(?:[^0-9]|$))',
            // 'year' => '([\[\(]?((?:19[0-9]|20[0-9])[0-9])[\]\)]?)',
            'year' => '([\[\(]?((?:19[0-9]|20[0-9])[0-9])[\]\)]?)',
            'resolution' => '([0-9]{3,4}p)',
            'quality' => '((?:PPV\.)?[HP]DTV|(?:HD)?CAM|B[DR]Rip|(?:HD-?)?TS|(?:PPV )?WEB-?DL(?: DVDRip)?|HDRip|DVDRip|DVDRIP|CamRip|W[EB]BRip|BluRay|DvDScr|hdtv|telesync)',
            'codec' => '(xvid|[hx]\.?26[45])',
            'audio' => '(MP3|DD5\.?1|Dual[\- ]Audio|LiNE|DTS|AAC[.-]LC|AAC(?:\.?2\.0)?|AC3(?:\.5\.1)?)',
            'group' => '(- ?([^-]+(?:-={[^-]+-?$)?))$',
            'region' => 'R[0-9]',
            'extended' => '(EXTENDED(:?.CUT)?)',
            'hardcoded' => 'HC',
            'proper' => 'PROPER',
            'repack' => 'REPACK',
            'container' => '(MKV|AVI|MP4)',
            'widescreen' => 'WS',
            'website' => '^(\[ ?([^\]]+?) ?\])',
            'language' => '(rus\.eng|ita\.eng)',
            'sbs' => '(?:Half-)?SBS',
            'unrated' => 'UNRATED',
            'size' => '(\d+(?:\.\d+)?(?:GB|MB))',
            '3d' => '3D'
        ];

        $this->types = [
            'season' => 'integer',
            'episode' => 'integer',
            'year' => 'integer',
            'extended' => 'boolean',
            'hardcoded' => 'boolean',
            'proper' => 'boolean',
            'repack' => 'boolean',
            'widescreen' => 'boolean',
            'unrated' => 'boolean',
            '3d' => 'boolean'
        ];
    }
    public function _part($name, $match, $raw, $clean){
        $this->parts[$name]=$clean;

        if(count($match)!=0){
            $index = strpos($this->torrent['name'], $match[0]);
            if($index==0){
                $this->start = count($match[0]);
            }else if($this->end == null || $index < $this->end){
                $this->end = $index;
            }
        }

        if($name != 'excess'){
            if($name == 'group'){
                $this->group_raw = $raw;
            }
            if($raw != null){
                $this->excess_raw = str_replace($raw, '', $this->excess_raw);
            }
        }
    }
    public function _late($name, $clean){
        if($name=='group'){
            $this->_part($name, [], null, $clean);
        }
        else if($name == 'episodeName'){
            $clean = preg_replace('/[\._]/', ' ', $clean);
            $clean = preg_replace('/_+$/', '', $clean);
            $this->_part($name, [], null, rtrim($clean));
        }
    }
    public function parse($name){
        $this->parts=[];
        $this->torrent = [
            'name' => $name
        ];
        $this->excess_raw=$name;
        $this->group_raw ='';
        $this->start = 0;
        $this->end = null;
        $this->title_raw = null;

        foreach($this->patterns as $key => $pattern){
            if(!in_array($key, ['season', 'episode', 'website', 'year'])){
                $pattern = '\\b'.$pattern.'\\b';
            }

            $clean_name = preg_replace('/_/', ' ', $this->torrent['name']);

            $match=[];
            preg_match_all('/'.$pattern.'/i', $clean_name, $match);
            if(count($match)==0){
                continue;
            }

            $match = $match[0];

            if(count($match)==0){
                continue;
            }

            $index=[];
            if($key === 'year' && count($match)>1){
                $nmatch = [];
                foreach($match as $m){
                    if($this->str_starts_with($m[0], '(')){
                        array_push($nmatch, $m);
                    }
                }

                if($nmatch){
                    $match = $nmatch;
                }
            }

            if(gettype($match[0]) == "array"){
                $match = (array)$match[0];
            }

            if(count($match)>1){
                $index['raw'] = 0;
                $index['clean'] = 1;
            }else{
                $index['raw'] = 0;
                $index['clean'] = 0;
            }

            if (in_array($key, array_keys($this->types)) && $this->types[$key]=='boolean'){
                $clean=true;
            }else{
                $clean = $match[$index['clean']];
                if (in_array($key, array_keys($this->types)) && $this->types[$key]=='integer'){
                    if($key == 'year' && $this->str_starts_with($clean, '(')){  //   Quick fix for the paranthesis problem due to intval("(1234)") return 0.
                         $clean = substr($clean, 1, -1);
                    }
                    $clean = intval($clean);
                }
            }

            if($key == 'group'){
                if(preg_match('/'.$this->patterns['codec'].'/i', $clean) || preg_match('/'.$this->patterns['quality'].'/i', $clean)){
                    continue;
                }
                if(preg_match('/[^ ]+ [^ ]+ .+/', $clean)){
                    $key = 'episodeName';
                }
            }

            if($key == 'episode'){
                $sub_pattern = $this->_escape_regex($match[$index['raw']]);
                $this->torrent['map']=preg_replace($sub_pattern, '{episode}', $this->torrent['name']);
            }

            $this->_part($key, $match, $match[$index['raw']], $clean);
        }

        $raw = $this->torrent['name'];
        if($this->end!=null){
            $subs=substr($raw, $this->start, $this->end - $this->start);
            $raw = explode('(', $subs)[0];
        }

        $clean = preg_replace('/^ -/', '', $raw);

        if (strpos($clean, ' ') === false && strpos($clean, '.') !== false){
            $clean = preg_replace('/\./', ' ', $clean);
        }
        $clean = preg_replace('/_/', ' ', $clean);
        $clean = rtrim(preg_replace('/([\[\(_]|- )$/', '', $clean));

        $this->_part('title', [], $raw, $clean);

        $clean = preg_replace('/(^[-\. ()]+)|([-\. ]+$)/', '', $this->excess_raw);
        $clean = preg_replace('/[\(\)\/]/', ' ', $clean);
        $match = preg_split('/\.\.+| +/', $clean);
        if(count($match)>0){
            $match = $match[0];
        }

        return $this->parts;
    }
}
