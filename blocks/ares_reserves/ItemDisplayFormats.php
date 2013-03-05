<?php
	// To add an item format, simply pick an unused format name and add a case for it in the switch below, then add the name and a description to the formatTypes array near the bottom of this file.
	// Please note that unless you are already familiar with PHP, you might want to read the "Item Formats" section in the readme.txt file that comes with the Ares addon for guidance.
	// A list item fields that can be accessed by using the $item->FieldName syntax can be found in the readme.
	function FormatItemText($item, $formatType)
	{
		// Each format type in the formatTypes array defined below should have a matching case statement here.  Inside each case statement, a string can be built and returned using the $item passed in.
		switch($formatType)
		{
			case "title":
				if(!empty($item->Title))
				{
					return "$item->Title";
				}
				else
				{
					return "No Title";
				}
				break;
			case "title/author":
				if(!empty($item->Title))
				{
					$text = $item->Title;
				}
				else
				{
					$text = "No Title";
				}
				if(!empty($item->Author))
				{
					$text = $text." - ".$item->Author;
				}
				return $text;
			case "title/articletitle":
				if(!empty($item->Title))
				{
					$text = $item->Title;
				}
				else
				{
					$text = "No Title";
				}
				if(!empty($item->ArticleTitle))
				{
					$text = $text." - ".$item->ArticleTitle;
				}
				return $text;
		}
	}
	
	// Entries in this array should have the format of "typeName" => "description".  For each entry in this array, there should be a matching case in the FormatItemText function.  For simplicity, I recommend using only lower case letters in the format type names.
	global $formatTypes;
	$formatTypes = array(
		"title" => "Item Title", 
		"title/author" => "Item Title - Author", 
		"title/articletitle" => "Item Title - Article Title");
?>
