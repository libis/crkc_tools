<pre>
<?php
include('header.php');

$t_func = new MyFunctions();
//$pn_locale_id = $t_func->idLocale("nl_NL");

//$o_db = new Db();
//$o_config = Configuration::load();

##########################################
# array met alle metadata-elements inladen
##########################################
$metadata = $t_func->loadMetadata();

/*
$xml          = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
 *
 */
$root_element = "adlibXML";
$xml         .= "<$root_element>";

$qr_entities = "SELECT entity_id, type_id, idno, access, status FROM ca_entities where deleted = 0 AND entity_id < 1000";

$entities = mysql_query($qr_entities);

if (!$entities) {
    die('Invalid query: ' . mysql_error());
}

if(mysql_num_rows($entities) > 0) {
    while($entities_array = mysql_fetch_assoc($entities)) {

        $entity = $entities_array['entity_id'];
        $xml .= "<record>";

        //loop through each key,value pair in row
        foreach($entities_array as $key => $value) {
            $xml .= "<$key>";
            $xml .= $value;
            $xml .= "</$key>";
        }

        ####################
        #De labels
        ####################
        $qr_labels = "SELECT forename, surname, is_preferred FROM ca_entity_labels WHERE entity_id = $entity";
        $entity_labels = mysql_query($qr_labels);

        if (!$entity_labels) {
            die('Invalid query: ' . mysql_error());
        }

        if(mysql_num_rows($entity_labels)>0) {
            while($entity_labels_array = mysql_fetch_assoc($entity_labels)) {
                $xml .= "<label>";

                //loop through each key,value pair in row
                foreach($entity_labels_array as $key_e => $value_e) {
                   $xml .= "<$key_e>";
                   $xml .= htmlspecialchars($value_e);
                   $xml .= "</$key_e>";
                }

                $xml.="</label>";
            }
        }

        ##############################
        # Attributes
        ##############################
        $qr_attributes = "SELECT attribute_id, element_id FROM ca_attributes WHERE table_num = 20 AND row_id = $entity";
        $attributes = mysql_query($qr_attributes);

        if (!$attributes) {
            die('Invalid query: ' . mysql_error());
        }

        if(mysql_num_rows($attributes) > 0) {
            while($attributes_array = mysql_fetch_assoc($attributes)) {

                $attribute = $attributes_array['attribute_id'];
                $tag = $metadata[$attributes_array['element_id']]['code'];

                $xml .= "<$tag>";

                ######################################
                # Attribute-values
                ######################################
                $qr_values = "SELECT element_id, value_longtext1 FROM ca_attribute_values WHERE attribute_id = $attribute";
                $values = mysql_query($qr_values);

                if (!$values) {
                    die('Invalid query: ' . mysql_error());
                }

                if(mysql_num_rows($values)>0) {
                    while($values_array = mysql_fetch_assoc($values)) {
                        $tag_v = $metadata[$values_array['element_id']]['code'];
                        $tab_v_list = $metadata[$values_array['element_id']]['list'];
                        $waarde = $values_array['value_longtext1'];

                        if (isset($tab_v_list)) {
                            $va_temp = $t_func->getListItemLabelById($tab_v_list, $waarde);
                            $waarde_new = $va_temp[0];
                        } else {
                            $waarde_new = $waarde;
                        }

                        $xml .= "<$tag_v>";
                        $xml .= htmlspecialchars($waarde_new);
                        $xml .= "</$tag_v>";

                        unset($tag_v);
                        unset($waarde);
                        unset($va_temp);
                        unset($waarde_new);
                    }
                }

                $xml .= "</$tag>";

                unset($attribute);
                unset($tag);
            }
        }

        ##############################
        # Relations
        ##############################
        $t_entity = new ca_entities($entity);
        $rel_entities = $t_entity->getRelatedItems('ca_entities');

        if (sizeof($rel_entities) > 0) {
            
        }

        unset($entity);
        $xml.="</record>";
    }
}


//close the root element
$xml .= "</$root_element>";

//send the xml header to the browser
header ("Content-Type:text/xml");

//output the XML data
echo $xml;
?>
</pre>