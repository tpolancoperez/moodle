<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course overview block
 *
 * Currently, just a copy-and-paste from the old My Moodle.
 *
 * @package   blocks
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/lib/weblib.php');
require_once($CFG->dirroot . '/lib/formslib.php');

class block_course_overview extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title   = get_string('pluginname', 'block_course_overview');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $USER, $CFG, $DB, $OUTPUT;
        if($this->content !== NULL) {
            return $this->content;
        }
        
        $url = $CFG->wwwroot.'/blocks/course_overview/';
        
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();

        
        if (eregi("MSIE",getenv("HTTP_USER_AGENT")) || eregi("Internet Explorer",getenv("HTTP_USER_AGENT"))) {
    		$content[] = '<center><a href="http://firefox.com" alt="Firefox" target="_blank"><img border=0 style="padding-top: 5px; padding-bottom:0px;" src="'.$url.'big_ie_warning.png"></a></center>';
    	}
    	
        
        $new = true;

        if ($mymoodle = $DB->get_record('elis_mymoodle', array('userid' => $USER->id))) {
        	$new = false;
        	$options = unserialize($mymoodle->options);
        	$msgs = unserialize($mymoodle->messages);
        	
        }
        
        $time = time();
        $sql = 'SELECT * FROM {elis_messages} WHERE starttime <= '.$time.' AND endtime >= '.$time;
    
        if ($messages = $DB->get_records_sql($sql)) {
        	$sql = 'SELECT id FROM {role_assignments} WHERE userid = '.$USER->id.' AND roleid IN (2, 8, 3) LIMIT 1';
        	if ($DB->count_records_sql($sql)) {
        		$teacher = true;
        	} else {
        		$teacher = false;
        	}
        	$sql = 'SELECT id FROM {role_assignments} WHERE userid = '.$USER->id.' AND roleid IN (4, 5) LIMIT 1';
        	if ($DB->count_records_sql($sql)) {
        		$student = true;
        	} else {
        		$student = false;
        	}
        	
        	$content[] = '
    	    <script type="text/javascript" language="javascript">
    	    	function closeMessage(mid) {
    	    		document.getElementById("message"+mid).className = "hide";
    	    		document.getElementById("messagex"+mid).innerHTML = "";
    	    		document.getElementById("hiddenmessages").className = "box OUhiddenmessagesmall OUhiddenmessagesmallcontent";
    	    		hiddenmessages.push(mid);
    	    		mcons[mid] = new AJAXConnection("aConnection");
    				mcons[mid].closeMessage(mid);
    	    		return false;
    	    	}
    	    	function showMessages() {
    	    		
    	    	}
    	    </script>
    	    <style type="text/css">
    	    	.OUmessage {
    	    		margin-bottom: 10px;
    	    		margin-top: 10px;
    	    		border-style:solid;
    				border-width:1px;
    				padding:10px;
    	    	}
    	    	
    	    	.OUmessageunhide {
    	    		margin-bottom: 7px;
    	    		margin-top: 7px;
    	    		border-style:dashed;
    				border-width:1px;
    				border-color:grey;
    				padding:10px;
    	    	}
    	    	
    	    	.OUhiddenmessage {
    	    		margin-bottom: 0px;
    	    		margin-top: 0px;
    	    		margin-left: 10px;
    	    		border: 0px;
    	    		color: #999;
    	    	}
    	    	
    	    	.OUhiddenmessagesmall {
    	    		margin-bottom: -5px;
    	    		margin-top: -5px;
    	    		margin-left: 10px;
    	    		border: 0px;
    	    		color: #999;
    	    	}
    	    	
    	    	.OUhiddenmessagesmallhide {
    	    		display: none;
    	    	}
    
    	    	
    	    </style>';
    	    
    		if (!is_array($msgs)) {
    	    	$msgs = array();
    	    }
    	    $mymessages = array();
    	    $hiddenmessages = array();
    	    $started = false;
    	    $hidden = false;
    	    foreach ($messages as $message) {
    	    	//1 = students
    	    	//2 = teachers
    	    	//3 = non-teachers
    	    	//4 = all
    	    	
    	    	if (($message->users == 4) || ($student && $message->users == 1) || (!$teacher && $message->users == 3) || ($teacher && $message->users == 2)) {
    	    		$mymessages[] = $message->id;
    	    		if ((isset($msgs[$message->id]) && $msgs[$message->id] == 0)) {
    	    			$hidden = true;
    	    			$thisclass = 'hide';
    	    			$thishide = true;
    	    			$hiddenmessages[] = $message->id;
    	    		} else {
    	    			$thisclass = 'OUmessage';
    	    			$thishide = false;
    	    		}
    	    		if (!$started) {
    	    			print_container_start(TRUE, '', 'messages');
    	    			$started = true;
    	    		}
    	    		print_simple_box_start('' ,'' ,'' ,5 , $thisclass, 'message'.$message->id);
    			    if (!$thishide) {
    				    $content[] = '<span id="messagex'.$message->id.'" alt="Remove Message" onClick="closeMessage('.$message->id.')" style="float: right; cursor: pointer"><img alt="Remove Message" src="'.$OUTPUT->pix_url('i/cross_red_big', '')->out().'"></span>';
    			    }
    			    $content[] = $message->message;
    			    
    			    print_simple_box_end();
    			    
    	    	}
    	    }
    	    if ($hidden) {
    	    	if (!$started) {
        			print_container_start(TRUE, '', 'messages');
        			$started = true;
        			
        		} else {
        			//print_simple_box_start('' ,'' ,'' ,5 , 'OUhiddenmessage', 'hiddenmessages');
        		}
        		
    	    }
    
    	    if ($started) {
    	    	if ($hidden) {
    	    		print_simple_box_start('' ,'' ,'' ,5 , 'OUhiddenmessagesmall', 'hiddenmessages');
    	    	} else {
    	    		print_simple_box_start('' ,'' ,'' ,5 , 'OUhiddenmessagesmallhide', 'hiddenmessages');
    	    	}
    	    	$content[] = '<span onClick="showMessages()" style="cursor: pointer;">To view hidden announcements, click here...</span>';
        		print_simple_box_end();
        		
        		print_simple_box_start('' ,'' ,'' ,5 , 'OUhiddenmessagesmallhide', 'unhiddenmessages');
    	    	$content[] = '<span onClick="rehideMessages()" style="cursor: pointer;">To collapse hidden announcements, click here...</span>';
        		print_simple_box_end();
        		
    		    print_container_end();
    	    }
    	    
    	    $content[] = '
    	    <script type="text/javascript" language="javascript">
    	    	function rehideMessages() {
    	    		hiddenmessages.forEach(rehideMessage);
    	    		
    	    		document.getElementById("unhiddenmessages").className = "box OUhiddenmessagesmallhide OUhiddenmessagesmallhidecontent";
    	    		document.getElementById("hiddenmessages").className = "box OUhiddenmessagesmall OUhiddenmessagesmallcontent";
    	    	}
    	    	
    	    	function rehideMessage(mid, index, array) {
    	    		document.getElementById("message"+mid).className = "hide";
    	    	}
    	    	
    	    	function showMessages() {
    	    		hiddenmessages.forEach(showMessage);
    	    		
    	    		document.getElementById("hiddenmessages").className = "box OUhiddenmessagesmallhide OUhiddenmessagesmallhidecontent";
    	    		document.getElementById("unhiddenmessages").className = "box OUhiddenmessagesmall OUhiddenmessagesmallcontent";
    	    	}
    	    	
    	    	function showMessage(mid, index, array) {
    	    		document.getElementById("message"+mid).className = "box OUmessageunhide OUmessageunhidecontent";
    	    	}
    	    </script>';
    
    	    
        }

        
        
        $cats = array();
	    $catcourses = array();
	    
	    $courses = enrol_get_my_courses(NULL, 'category DESC,visible DESC,sortorder ASC');
	    
	    
	    $catid = -99;
	    
	    
	    //Break into categories
	    foreach ($courses as $course) {
	    	$catid = $course->category;
	    	if (!isset($catcourses[$catid])) {
	    		$catcourses[$catid] = array();
	    	}
	    	$catcourses[$catid][$course->id] = $course;
	    }
	    
	    $pixClosed = $OUTPUT->pix_url('i/closed', '')->out();
	    $pixOpen = $OUTPUT->pix_url('i/open', '')->out();

	    foreach ($catcourses as $catid => $catcourse) {
	        
            
            $cats[] = $catid;
            
            $title = $DB->get_field('course_categories', 'name', array('id' => $catid));
            $link = $title;

            //print '<span id="catbox'.$catid.'"><span id="catboxflag'.$catid.'"></span><h3 onClick="loadCat('.$catid.')"><img src="'.$CFG->pixpath.'/i/closed.gif">'.$link.'</h3>';
            $content[] = '<span id="catbox'.$catid.'"><span id="catboxflag'.$catid.'"></span><h3 onClick="loadCat('.$catid.')"><span id="catboximg'.$catid.'"><img src="'.$pixClosed.'"></span>'.$link.'<span id="cattitleclick'.$catid.'"> - Click to show  courses</span></h3>';
            
            $content[] = '<span class="" id="catboxhd'.$catid.'"></span>';
            
            /*if ($mymoodle && $mymoodle->userchanged) {
            	print '<span class="" id="catboxhd'.$catid.'"></span>';
            } else {
            	print '<span onClick="loadCat('.$catid.')" class="" id="catboxhd'.$catid.'"> - Click here to show courses</span>';
            }*/
            
            $content[] = '</span>';

	    }
	    
	    
	    if ($new) {
            $changed = false;
			$options = array();
			foreach ($cats as $cat) {
                $options[$cat] = 1;
			}
		} else {
			$changed = false;
			foreach ($cats as $cat) {
				if (!isset($options[$cat])) {
					$options[$cat] = 1;
					$changed = true;
				}
			}
		}
		
		
		if ($changed) {
			$mymoodle->options = serialize($options);
			$DB->update_record('elis_mymoodle', $mymoodle);
		} elseif ($new) {
			$mymoodle = new Object();
			$mymoodle->userid = $USER->id;
			$mymoodle->options = serialize($options);
			$DB->insert_record('elis_mymoodle', $mymoodle);
		}
		
		$hiddenmessages = array(); //Placeholder
		
		//$content[] = container_start('', 'jswarning');
    	//print_simple_box_start('center', '100%', '', 5, "coursebox", '');
	    $content[] =  '<span id="jswarning"><div class="clearfix" style="">
	    		<div style="float: left;">
	    			<img src="'.$url.'Caution-Icon.png" alt="Caution">
	    		</div>
	    		<div style="float: left;">
	    			<h2>JavaScript not enabled.</h2>
	    			JavaScript must be enabled for this page to display properly. To view your courses without JavaScript, <a href="?categories=all" alt="All courses">Click Here</a>.
	    		</div>
	    		</div></span>';
	    //print_simple_box_end();
	    //print_container_end();
	    //$content[] = container_end();
	    
	    $content[] =  '
	    <script type="text/javascript" language="javascript">
			function AJAXConnection(name) {
				this.className = "AJAXConnection";
				var request;
				
				{    
			        this.name = name;
			    }
	
		    	this.closeCat = function (cid) {
		    		var request;
					var self = this;
					try {
					    self.request = new XMLHttpRequest();
					  } catch (trymicrosoft) {
					    try {
					      self.request = new ActiveXObject("Msxml2.XMLHTTP");
					    } catch (othermicrosoft) {
					      try {
					        self.request = new ActiveXObject("Microsoft.XMLHTTP");
					      } catch (failed) {
					        self.request = false;
					      }
					    }
					  }
					
					  if (!self.request)
					    alert("Error initializing XMLHttpRequest!");
					
					
					
					
					var url = "'.$url.'partial.php?action=close&categories="+cid;
					self.request.open("GET", url, true);
					//request.onreadystatechange = updateCat;
					self.request.onreadystatechange = self.request.onreadystatechange = function() {  _callBackFunctionClose(self.request); };
	        
					self.request.send(null);
		    	}
		    	
		    	this.closeMessage = function (mid) {
		    		var request;
					var self = this;
					try {
					    self.request = new XMLHttpRequest();
					  } catch (trymicrosoft) {
					    try {
					      self.request = new ActiveXObject("Msxml2.XMLHTTP");
					    } catch (othermicrosoft) {
					      try {
					        self.request = new ActiveXObject("Microsoft.XMLHTTP");
					      } catch (failed) {
					        self.request = false;
					      }
					    }
					  }
					
					  if (!self.request)
					    alert("Error initializing XMLHttpRequest!");
					
					
					
					document.getElementById("hiddenmessages").className = "OUhiddenmessagesmall";
					var url = "'.$url.'partial.php?action=close&message="+mid;
					self.request.open("GET", url, true);
					self.request.onreadystatechange = self.request.onreadystatechange = function() {  _callBackFunctionClose(self.request);  };
		        	self.request.send(null);
		    	}
		    	
		    	
				this.loadCat = function (cid, auto) {
					var request;
					var self = this;
					try {
					    self.request = new XMLHttpRequest();
					  } catch (trymicrosoft) {
					    try {
					      self.request = new ActiveXObject("Msxml2.XMLHTTP");
					    } catch (othermicrosoft) {
					      try {
					        self.request = new ActiveXObject("Microsoft.XMLHTTP");
					      } catch (failed) {
					        self.request = false;
					      }
					    }
					  }
					
					  if (!self.request)
					    alert("Error initializing XMLHttpRequest!");
					
					
					document.getElementById("catboxhd"+cid).className = "";
					document.getElementById("catboxhd"+cid).innerHTML = "<img src=\"'.$url.'loading.gif\">Loading...";
					
					if (auto) {
					   var url = "'.$url.'partial.php?auto=true&categories="+cid;
					} else {
					   var url = "'.$url.'partial.php?categories="+cid;
					}
					
					self.request.open("GET", url, true);
					//request.onreadystatechange = updateCat;
					self.request.onreadystatechange = self.request.onreadystatechange = function() { 
	            _callBackFunctionOpen(self.request); 
	        };
	
					self.request.send(null);
					
				}
				
				_callBackFunctionOpen = function (http_request) {
					if (http_request.readyState == 4) {
						if (http_request.status == 200) {
					         var response = http_request.responseText.split("|||");
					         var id = response[0];
					         
					         document.getElementById("catboxflag"+id).className = "hide";
					         document.getElementById("cattitleclick"+id).className = "hide";
					         document.getElementById("catboxhd"+id).innerHTML = response[1];
					         document.getElementById("catboximg"+id).innerHTML = "<img src=\"'.$pixOpen.'\">";
					       } else {
					         //alert("status is " + http_request.status);
					       }
					}
				}
				
				_callBackFunctionClose = function (http_request) {}
	
			};
			
			
			var hiddenmessages = ['.implode(',', $hiddenmessages).'];
			var cons = [];
			var mcons = [];
			var a;
			
			function loadCat(cid, auto) {

				if (document.getElementById("catboxflag"+cid).className == "hide") {
					document.getElementById("catboxflag"+cid).className = "";
					document.getElementById("cattitleclick"+cid).className = "";
			        document.getElementById("catboxhd"+cid).innerHTML = "";// - Click here to show courses
			        document.getElementById("catboxhd"+cid).onclick = "";
			        document.getElementById("catboximg"+cid).innerHTML = "<img src=\"'.$pixClosed.'\">";
			        cons[cid] = new AJAXConnection("aConnection");
					cons[cid].closeCat(cid);
				} else {
					cons[cid] = new AJAXConnection("aConnection");
					cons[cid].loadCat(cid, auto);
				}
				
				
			}
			
			
			window.onload = function() {
				try {
					document.getElementById("jswarning").className="hide";
					document.getElementById("coursesbox").className="wrap wraplevel2 ";
				} catch (e) {
				}
				';
		$i = 0;
		foreach ($cats as $cat) {
		//print_r($options);
			if (isset($options[$cat]) && $options[$cat] == 1) {
				$content[] = 'try {
					setTimeout("loadCat('.$cat.', true)", '.($i * 100).');
				} catch (e) {
				}';
				$i++;
			}
		}
		$content[] = '		};
		</script>    
	    
	    ';

        /*// limits the number of courses showing up
        $courses_limit = 21;
        // FIXME: this should be a block setting, rather than a global setting
        if (isset($CFG->mycoursesperpage)) {
            $courses_limit = $CFG->mycoursesperpage;
        }

        $morecourses = false;
        if ($courses_limit > 0) {
            $courses_limit = $courses_limit + 1;
        }

        $courses = enrol_get_my_courses('id, shortname, modinfo', 'visible DESC,sortorder ASC', $courses_limit);
        $site = get_site();
        $course = $site; //just in case we need the old global $course hack

        if (is_enabled_auth('mnet')) {
            $remote_courses = get_my_remotecourses();
        }
        if (empty($remote_courses)) {
            $remote_courses = array();
        }

        if (($courses_limit > 0) && (count($courses)+count($remote_courses) >= $courses_limit)) {
            // get rid of any remote courses that are above the limit
            $remote_courses = array_slice($remote_courses, 0, $courses_limit - count($courses), true);
            if (count($courses) >= $courses_limit) {
                //remove the 'marker' course that we retrieve just to see if we have more than $courses_limit
                array_pop($courses);
            }
            $morecourses = true;
        }


        if (array_key_exists($site->id,$courses)) {
            unset($courses[$site->id]);
        }

        foreach ($courses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $courses[$c->id]->lastaccess = 0;
            }
        }

        if (empty($courses) && empty($remote_courses)) {
            $content[] = get_string('nocourses','my');
        } else {
            ob_start();

            require_once $CFG->dirroot."/course/lib.php";
            print_overview($courses, $remote_courses);

            $content[] = ob_get_contents();
            ob_end_clean();
        }

        // if more than 20 courses
        if ($morecourses) {
            $content[] = '<br />...';
        }*/

        $this->content->text = implode($content);

        return $this->content;
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my-index'=>true);
    }
}
?>
