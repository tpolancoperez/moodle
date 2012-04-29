<?php
//
//  Copyright (c) 2011, Maths for More S.L. http://www.wiris.com
//  This file is part of Moodle WIRIS Plugin.
//
//  Moodle WIRIS Plugin is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Moodle WIRIS Plugin is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with Moodle WIRIS Plugin. If not, see <http://www.gnu.org/licenses/>.
//

defined('MOODLE_INTERNAL') || die();

//------------------------------------------------------------------------------//
// General description:                                                         //
// Applet: An applet is a Java program designed to be executed in a web page    //
// through a navigator who supports Java. All the last versions of Netscape or  //
// Microsoft Internet Explorer include it by defect.                            //
// WIRIS CAS (Computer Algebra System): mathematical calculation Tool that      //
// works through an applet.                                                     //
// Web Service: A web service is a component of software that can describe      //
// itself and provides certain functionality to other applications, through an  //
// Internet connection.                                                         //
// WIRIS EDITOR: Formulas publisher who allows to generate images of these      //
// formulas through a Web Service.                                              //
// Regular expression: A regular expression is a model or a form to compare     //
// with a chain of characters.                                                  //
//                                                                              //
// Wiris Filter Description:                                                    //
// It is a filter that allows to visualize applets that use WIRIS CAS and       //
// images (of formulas) generated through the WIRIS Formula Image Service.      //
//                                                                              //
// Replaces all substrings ''«applet ... «/applet»' by the corresponding WIRIS  //
// applet code: '<applet ... </applet>'                                         //
// Replaces all substrings '«math ... «/math»' by the corresponding MathML      //
// code: '<math ... </math>'                                                    //
//------------------------------------------------------------------------------//


class filter_wiris extends moodle_text_filter {

	public $TAGS;
	
	public function filter($text, array $options = array()) {
		global $CFG;
		
		filter_wiris::WF_initfilter();
		
		// Perform transformations if necessary
		//if ($CFG->filter_wiris_cas_enable){
			$text = filter_wiris::WF_filter_applet($text);
		//}
		//if ($CFG->filter_wiris_editor_enable){
			$text = filter_wiris::WF_filter_math($text,false);
		//}
		return $text;
	}
	
	function WF_initfilter(){
		global $CFG;
		require_once($CFG->libdir . '/textlib.class.php');
		/*
		 * Converting all 'tags' (markups) to the current charset.
		 * This php file is encoded in ISO-8859-1, so all the string defined 
		 * in this file are also encoded in ISO-8859-1. For example, the 
		 * string '«' has a length of 1 byte. 
		 * Problems occured when the filtered content is encoded with another charset.
		 * For example in UTF-8 the string '«' is encoded with two bytes. 
		 * The searched strings (such as '«applet') must be encoded with the 
		 * same charset as the filtered content.
		 */
		$convertor = textlib_get_instance();
		$this->TAGS->in_open = $convertor->convert('«', 'iso-8859-1');
		$this->TAGS->in_close = $convertor->convert('»', 'iso-8859-1');
		$this->TAGS->in_entity = $convertor->convert('§', 'iso-8859-1');
		$this->TAGS->in_appletopen = $convertor->convert('«applet', 'iso-8859-1');
		$this->TAGS->in_appletclose = $convertor->convert('«/applet»', 'iso-8859-1');			
		$this->TAGS->in_mathopen = $convertor->convert('«math', 'iso-8859-1');
		$this->TAGS->in_mathclose = $convertor->convert('«/math»', 'iso-8859-1');		
		$this->TAGS->in_double_quote = $convertor->convert('¨', 'iso-8859-1');
		$this->TAGS->out_open = '<';
		$this->TAGS->out_close = '>';
		$this->TAGS->out_entity = '&';
		$this->TAGS->out_double_quote = '"';
	}
	
	function WF_filter_applet($text){
		
		$output = ''; 
		$n0 = 0;
		// Search for '«applet'. If it is not found, the
		// content is returned without any modification
		
		$n1 = stripos($text, $this->TAGS->in_appletopen);

		if($n1 === false) {
			return $text; // directly return the content
		}
		
		// filtering
		while($n1 !== false) {
		
			$output .= substr($text, $n0, $n1 - $n0);
			
			$n0 = $n1;
			$n1 = stripos($text, $this->TAGS->in_appletclose, $n0);
			if(!$n1) {
				break;
			}
			$n1 = $n1 + strlen($this->TAGS->in_appletclose);
			// Getting the substring «applet ... «/applet»
			$sub = substr($text, $n0, $n1 - $n0);
			
			/*
			 * This filter does the following replacement inside the <math> tags.
			 *   <a href="<url>">blabla</a>  -->  <url>
			 * 
			 * The reason is that Moodle replaces URL's with HTML links ('A' tags) and ignores the <span class="nolink"> tag.
			 */
			$pattern = '/<a href="[^"]*" target="_blank">([^<]*)<\/a>/';
			$replacement = '\1';
			$sub = preg_replace($pattern, $replacement, $sub);

			// replacing '¨' by '"'
			$sub = str_replace($this->TAGS->in_double_quote, $this->TAGS->out_double_quote, $sub);
			// replacing '§' by '&'
			$sub = str_replace($this->TAGS->in_entity, $this->TAGS->out_entity, $sub);
			// replacing '«' by '<'
			$sub = str_replace($this->TAGS->in_open, $this->TAGS->out_open, $sub);
			// replacing '»' by '>'
			$sub = str_replace($this->TAGS->in_close, $this->TAGS->out_close, $sub);
			
			$output .= $sub;
			
			$n0 = $n1;
			// searching next '«applet'
			$n1 = stripos($text, $this->TAGS->in_appletopen, $n0);
		}
		$output .= substr($text, $n0);
		return $output;
	}	
	
	function WF_filter_math($text,$editor) {
		
		$output = ''; 
		$n0 = 0;
		// Search for '«math'. If it is not found, the
		// content is returned without any modification
		$n1 = stripos($text, $this->TAGS->in_mathopen);
		
		$start = 0;
		$end = 0;
		
		if($n1 === false) {
			return $text; // directly return the content
		}
		
		// filtering
		while($n1 !== false) {
            $inimg = false;
            
            if ($start = strrpos(substr($text, $n0, $n1 - $n0), '<img')) {
                $inimg = true;
                if ($endpos = strrpos(substr($text, $n0, $n1 - $n0), '>')) {
                    if ($endpos > $start) {
                        $start = false;
                    }
                }
                
            }
            if (!$start) {
                $inimg = false;
                $start = $n1;
            } 
            
			$output .= substr($text, $end, $start - $end);

			$n0 = $n1;
			$n1 = stripos($text, $this->TAGS->in_mathclose, $n0);
			if(!$n1) {
				break;
			}
			$n1 = $n1 + strlen($this->TAGS->in_mathclose);
			
			if ($inimg) {
                $end = strpos($text, '>', $n1);
                $end += 1;
			} else {
                $end = $n1;
			}
			
			// Getting the substring «math ... «/math»
			$sub = substr($text, $n0, $n1 - $n0);
			
			/*
			 * This filter does the following replacement inside the <math> tags.
			 *   <a href="<url>">blabla</a>  -->  <url>
			 * 
			 * The reason is that Moodle replaces URL's with HTML links ('A' tags) and ignores the <span class="nolink"> tag.
			 */
			$pattern = '/<a href="[^"]*" target="_blank">([^<]*)<\/a>/';
			$replacement = '\1';
			$sub = preg_replace($pattern, $replacement, $sub);

			
			$sub = html_entity_decode($sub, ENT_COMPAT);
			// replacing '¨' by '"'
			$sub = str_replace($this->TAGS->in_double_quote, $this->TAGS->out_double_quote, $sub);			
			// replacing '«' by '<'
			$sub = str_replace($this->TAGS->in_open, $this->TAGS->out_open, $sub);
			// replacing '»' by '>'
			$sub = str_replace($this->TAGS->in_close, $this->TAGS->out_close, $sub);
			// replacing '§' by '&'
			$sub = str_replace($this->TAGS->in_entity, $this->TAGS->out_entity, $sub);
			// generate the image code
			$sub = filter_wiris::WF_math_image($sub,$editor);
			// appending the modified substring
			$output .= $sub;
			$n0 = $n1;
			// searching next '«math'
			$n1 = stripos($text, $this->TAGS->in_mathopen, $n0);
		}
		$output .= substr($text, $end);
		return $output;
	}	
	
	/*
	 * Generate the html IMG code corresponding to the specified MathML expression
	 */
	function WF_math_image($mathml, $editor) {
		global $CFG;
		global $lang;
		global $DB;
		
		
		include $CFG->dirroot . '/lib/editor/tinymce/version.php';
		//include_once $CFG->dirroot . '/lib/editor/tinymce/tiny_mce/' . $plugin->release . '/plugins/tiny_mce_wiris/integration/api.php';
		
		
		if (is_file($CFG->dirroot . '/lib/editor/tinymce/tiny_mce/' . $plugin->release . '/plugins/tiny_mce_wiris/integration/api.php')) { 
			include_once $CFG->dirroot . '/lib/editor/tinymce/tiny_mce/' . $plugin->release . '/plugins/tiny_mce_wiris/integration/api.php';
			$api = new com_wiris_plugin_PluginAPI;
			$src = $api->mathml2img($mathml, $CFG->wwwroot . "/lib/editor/tinymce/tiny_mce/" . $plugin->release . "/plugins/tiny_mce_wiris/integration");
			$output = '<img align="middle" src="';
			$output .= $src;
			$output .= '" />'; 
		}else{
			$output = 'WIRIS Plugin for TinyMce not installed.';
		}
		

		/*
		//We add the title tooltip only when the function is called to insert images to the editor
		if($editor===true) {
			if($formula == 'true'){
				//Where is this parameter?
				$title=wrs_get_string($lang, 'wiristitletext');
				$title=utf8_encode($title); 
				$output .= '" title ="'.$title;
			}
		}
		*/

		
		return $output;
	}
}
