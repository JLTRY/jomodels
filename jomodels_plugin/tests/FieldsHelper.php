<?php
namespace Joomla\Component\Fields\Administrator\Helper;
class FieldsHelper
{
    /**
     * Method to extract key/value pairs out of a string with XML style attributes
     *
     * @param   string  $string  String containing XML style attributes
     *
     * @return  array  Key/Value pairs for the attributes
     *
     * @since   1.7.0
     */
    public static function getFields($component, $user, $display)
    {
        return array((object)array("name" => "photos", "value" => 'http://jltryoen.fr/index.php?option=com_jogallery&amp;view=foldergroup&amp;parent=0&amp;Itemid=0&amp;id=2&amp;header=1'),
                    (object)array("name" => "image-photos", "value" => '<img loading="lazy" width="669" height="228" src="images/phocagallery/annees.jpg" alt="">'),
                    (object)array("name"=> "titre-photos", "value" => 'Ann&eacute;es'));
    }
}
