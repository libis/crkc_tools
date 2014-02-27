<?php
include('header.php');

//$t_func = new MyFunctions();
$t_list = new ca_lists();
$gewenst = 'PLA';

##########################################
# array met alle metadata-elements inladen
##########################################
//$metadata = $t_func->loadMetadata();

# Collections (ca_collections, ca_storage_locations*)
if ($gewenst === 'COL' || $gewenst === 'ALL') {
    $t_object = new ca_collections();
    $table_name = 'ca_collections';
    $table_nr = 13;
    $table_type = $t_list->getListID('collection_types'); //list_id=27
    echo $table_type;
    $qr_table = "SELECT collection_id, type_id, idno, access, status FROM ca_collections where deleted = 0 AND UPPER(idno) like 'POV%' ";
    $eenheid = 'collection_id';
    $qr_labels = "SELECT name, is_preferred FROM ca_collection_labels WHERE collection_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# op basis van objects # Entities (ca_collections*, ca_entities*, ca_occurrences, ca_places*, ca_storage_locations?)
if ($gewenst === 'ENT' || $gewenst === 'ALL') {
    $t_object = new ca_entities();
    $table_name = 'ca_entities';
    $table_nr = 20;
    $table_type = $t_list->getListID('entity_types'); //list_id=18
    echo $table_type;
    $qr_table = "SELECT entity_id, type_id, idno, access, status FROM ca_entities where deleted = 0";
    $eenheid = 'entity_id';
    $qr_labels = "SELECT displayname, forename, surname, is_preferred FROM ca_entity_labels WHERE entity_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# volledig leveren # ListItems (list_items)
if ($gewenst === 'LIS' || $gewenst === 'ALL') {
    $t_object = new ca_lists();
    $table_name = 'ca_lists';
    $table_nr = 36;
    $table_type = $t_list->getListID('set_types'); //list_id=3
    echo $table_type;
    $qr_table = "SELECT list_id, list_code, is_system_list, is_hierarchical, use_as_vocabulary FROM ca_lists ";
    $eenheid = 'list_id';
    $qr_labels = "SELECT name FROM ca_list_labels WHERE list_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# niet leveren # Loans (ca_collections, ca_entities*, ca_loans, ca_objects*, ca_occurrences, ca_places, ca_storage_locations)
if ($gewenst === 'LOA' || $gewenst === 'ALL') {
    $t_object = new ca_loans();
    $table_name = 'ca_loans';
    $table_nr = 133;
    $table_type = $t_list->getListID('loan_types'); //list_id=16
    echo $table_type;
    $qr_table = "SELECT loan_id, type_id, idno, status FROM ca_loans where deleted = 0 ";
    $eenheid = 'loan_id';
    $qr_labels = "SELECT name, is_preferred FROM ca_loan_labels WHERE loan_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# Objects (ca_collections*, ca_entities*, ca_objects*, ca_occurrences*, ca_places*, ca_storage_locations*)
if ($gewenst === 'OBJ' || $gewenst === 'ALL') {
    $t_object = new ca_objects();
    $table_name = 'ca_objects';
    $table_nr = 57;
    $table_type = $t_list->getListID('object_types'); //list_id=6
    echo $table_type;
    $qr_table = "SELECT object_id, type_id, idno, access, status FROM ca_objects where deleted = 0 AND UPPER(idno) like 'POV%' AND object_id <= 50000 ";
    $eenheid = 'object_id';
    $qr_labels = "SELECT name, is_preferred FROM ca_object_labels WHERE object_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# volledig # Occurrences (ca_collections, ca_occurrences*, ca_storage_locations) (verzekeringsdocs verwijderen !!!!!)
if ($gewenst === 'OCC' || $gewenst === 'ALL') {
    $t_object = new ca_occurrences();
    $table_name = 'ca_occurrences';
    $table_nr = 67;
    $table_type = $t_list->getListID('occurrence_types'); //list_id=24
    echo $table_type;
    $qr_table = "SELECT occurrence_id, type_id, idno, access, status FROM ca_occurrences where deleted = 0";
    $eenheid = 'occurrence_id';
    $qr_labels = "SELECT name, is_preferred FROM ca_occurrence_labels WHERE occurrence_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# volledig # Places (ca_collections, ca_occurrences, ca_places, ca_storage_locations)
if ($gewenst === 'PLA' || $gewenst === 'ALL') {
    $t_object = new ca_places();
    $table_name = 'ca_places';
    $table_nr = 72;
    $table_type = $t_list->getListID('place_types'); //list_id=21
    echo $table_type;
    $qr_table = "SELECT place_id, parent_id, type_id, idno, access, status FROM ca_places where deleted = 0";
    $eenheid = 'place_id';
    $qr_labels = "SELECT name, is_preferred FROM ca_place_labels WHERE place_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# niet # Sets (users, user_groups)
if ($gewenst === 'SET' || $gewenst === 'ALL') {
    $t_object = new ca_sets();
    $table_name = 'ca_sets';
    $table_nr = 103;
    $table_type = $t_list->getListID('set_types'); //list_id=3
    echo $table_type;
    $qr_table = "SELECT set_id, user_id, type_id, set_code, table_num, status, access FROM ca_sets where deleted = 0";
    $eenheid = 'set_id';
    $qr_labels = "SELECT name FROM ca_set_labels WHERE set_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# enkel POV # Storage_locations (storage_locations)
if ($gewenst === 'STO' || $gewenst === 'ALL') {
    $t_object = new ca_storage_locations();
    $table_name = 'ca_storage_locations';
    $table_nr = 89;
    $table_type = $t_list->getListID('storage_location_types'); //list_id=30
    echo $table_type;
    $qr_table = "SELECT location_id, parent_id, type_id, idno, status FROM ca_storage_locations where deleted = 0";
    $eenheid = 'location_id';
    $qr_labels = "SELECT name, is_preferred FROM ca_storage_location_labels WHERE location_id = ";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# beperken op usergroups met pov% #Users (ca_user_groups*, ca_user_roles*)
if ($gewenst === 'USE' || $gewenst === 'ALL') {
    $t_object = new ca_users();
    $table_name = 'ca_users';
    $table_nr = 94;
    $table_type = "";
    $qr_table = "SELECT user_id, user_name, password, fname, lname, email, active, entity_id FROM ca_users";
    $eenheid = 'user_id';
    $qr_labels = "";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# User groups
if ($gewenst === 'GRO' || $gewenst === 'ALL') {
    $t_object = new ca_user_groups();
    $table_name = 'ca_user_groups';
    $table_nr = 91;
    $table_type = "";
    $qr_table = "SELECT group_id, parent_id, name, code, description FROM ca_user_groups where UPPER(name) like 'POV%' ";
    $eenheid = 'group_id';
    $qr_labels = "";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}
# User roles
if ($gewenst === 'ROL' || $gewenst === 'ALL') {
    $t_object = new ca_user_roles();
    $table_name = 'ca_user_roles';
    $table_nr = 92;
    $table_type = "";
    $qr_table = "SELECT role_id, name, code, description FROM ca_user_roles ";
    $eenheid = 'role_id';
    $qr_labels = "";
    XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels, $t_object);
    unset($t_object);
}

#
#
#

function XML_Download($table_name, $table_nr, $table_type, $qr_table, $eenheid, $qr_labels_old, $t_object) {

    $logDir = "/www/libis/web/lias_html/ca_crkc/crkc_tools/log/";
    $file = $logDir.$table_name.".xml";

    $t_func = new MyFunctions();

    ##########################################
    # array met alle metadata-elements inladen
    ##########################################
    $metadata = $t_func->loadMetadata();

    $xml          = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $root_element = "adlibXML";
    $xml         .= "<$root_element>\n";

    file_put_contents($file, $xml);

    $table = mysql_query($qr_table);

    if (!$table) {
        die('Invalid query: ' . mysql_error());
    }

    if(mysql_num_rows($table) > 0) {
        while($table_array = mysql_fetch_assoc($table)) {
            $prim_key = $table_array[$eenheid];
            $xml = "<record>";

            foreach($table_array as $key => $value) {
                $xml .= "<$key>";
                if ($key === 'type_id') {
                    $value_new = $t_func->getListItemLabelById($table_type, $value);
                    $xml .= htmlspecialchars($value_new[0]);
                } else {
                    $xml .= htmlspecialchars($value);
                }
                $xml .= "</$key>";
            }

            ####################
            #De labels
            ####################
            if (strlen($qr_labels_old) > 0) {
                $qr_labels = $qr_labels_old.$prim_key;
                $table_labels = mysql_query($qr_labels);

                if (!$table_labels) {
                    die('Invalid query: ' . mysql_error());
                }

                if(mysql_num_rows($table_labels)>0) {
                    while($table_labels_array = mysql_fetch_assoc($table_labels)) {
                        $xml .= "<label>";

                        foreach($table_labels_array as $key_e => $value_e) {
                            if (isset($value_e)) {
                                $xml .= "<$key_e>";
                                $xml .= htmlspecialchars($value_e);
                                $xml .= "</$key_e>";
                            }
                        }
                        $xml.="</label>";
                    }
                }
            }
            ##############################
            # Attributes
            ##############################
            $qr_attributes = "SELECT attribute_id, element_id FROM ca_attributes WHERE table_num = $table_nr AND row_id = $prim_key";
            $attributes = mysql_query($qr_attributes);

            if (!$attributes) {
                die('Invalid query: ' . mysql_error());
            }

            if(mysql_num_rows($attributes) > 0) {
                while($attributes_array = mysql_fetch_assoc($attributes)) {

                    $attribute = $attributes_array['attribute_id'];
                    $tag = $metadata[$attributes_array['element_id']]['code'];

                    ######################################
                    # Attribute-values
                    ######################################
                    $qr_values = "SELECT element_id, value_longtext1 FROM ca_attribute_values WHERE attribute_id = $attribute";
                    $values = mysql_query($qr_values);
                    $xmlbis = "";

                    if (!$values) {
                        die('Invalid query: ' . mysql_error());
                    }

                    if(mysql_num_rows($values)>0) {
                        while($values_array = mysql_fetch_assoc($values)) {
                            if (isset($values_array['value_longtext1'])) {
                                $tag_v = $metadata[$values_array['element_id']]['code'];
                                $tab_v_list = $metadata[$values_array['element_id']]['list'];
                                $waarde = $values_array['value_longtext1'];

                                if (isset($tab_v_list)) {
                                    $va_temp = $t_func->getListItemLabelById($tab_v_list, $waarde);
                                    $waarde_new = $va_temp[0];
                                } else {
                                    $waarde_new = $waarde;
                                }

                                $xmlbis .= "<$tag_v>";
                                $xmlbis .= htmlspecialchars($waarde_new);
                                $xmlbis .= "</$tag_v>";

                                unset($tag_v);
                                unset($waarde);
                                unset($va_temp);
                                unset($waarde_new);
                            }
                        }
                    }
                    if (strlen($xmlbis) > 0) {
                        $xml .= "<$tag>";
                        $xml .= $xmlbis;
                        $xml .= "</$tag>";
                    }

                    unset($xmlbis);
                    unset($attribute);
                    unset($tag);
                }
            }

            ##############################
            # Relationships
            ##############################

            if ($table_name === 'ca_collections') {
                $relationships = array('ca_collections', 'ca_storage_locations');
            } elseif ($table_name === 'ca_entities') {
                $relationships = array('ca_collections', 'ca_entities',  'ca_occurrences','ca_places', 'ca_storage_locations');
            } elseif ($table_name === 'ca_loans') {
                $relationships = array('ca_collections', 'ca_entities', 'ca_loans', 'ca_objects', 'ca_occurrences','ca_places', 'ca_storage_locations');
            } elseif ($table_name === 'ca_objects') {
                $relationships = array('ca_collections', 'ca_entities', 'ca_objects', 'ca_occurrences', 'ca_places', 'ca_storage_locations');
            } elseif ($table_name === 'ca_occurrences') {
                $relationships = array('ca_collections', 'ca_occurrences', 'ca_storage_locations');
            } elseif ($table_name === 'ca_places') {
                $relationships = array('ca_collections', 'ca_occurrences', 'ca_places', 'ca_storage_locations');
            } elseif ($table_name === 'ca_sets') {
                $relationships = array('ca_users', 'ca_user_groups');
            } elseif ($table_name === 'ca_storage_locations') {
                $relationships = array('ca_storage_locations');
            } elseif ($table_name === 'ca_users') {
                $relationships = array('ca_user_groups', 'ca_user_roles');
            }
            foreach($relationships as $value) {
                $t_object->load($prim_key);
                $va_relations = $t_object->getRelatedItems($value);

                if (sizeof($va_relations) > 0 ) {
                    echo "JA ".$value." - ".$prim_key."\n";
                    foreach($va_relations as $result) {
                        $xml .= "<$value>";
                        foreach($result as $sleutel => $waarde) {
                            if (($sleutel === $eenheid) || ($sleutel === 'idno') || ($sleutel === 'label') ||
                                ($sleutel === 'relationship_typename') || ($sleutel === 'direction')) {
                                $xml .= "<$sleutel>";
                                $xml .= htmlspecialchars($waarde);
                                $xml .= "</$sleutel>";
                            }
                        }
                        $xml .= "</$value>";
                    }
                }
            }

            ##############################
            # Items
            ##############################

            # ca_set_items

            if ( ($table_name === 'ca_sets') || ($table_name === 'ca_lists') ) {
                if ($table_name === 'ca_sets') {
                    $qr_items = "SELECT item_id, row_id, table_num FROM ca_set_items WHERE set_id = $prim_key";
                } elseif ($table_name === 'ca_lists') {
                    $qr_items = "SELECT item_id, parent_id, idno, item_value, access, status FROM ca_list_items WHERE list_id = $prim_key AND deleted = 0 ";
                }
                $items = mysql_query($qr_items);

                if (!$items) {
                    die('Invalid query: ' . mysql_error());
                }

                if(mysql_num_rows($items) > 0) {
                    $xml .= "<items>";
                    while($items_array = mysql_fetch_assoc($items)) {
                        $xml .= "<item>";
                        foreach($items_array as $sleutel => $waarde) {
                            $xml .= "<$sleutel>";
                            $xml .= htmlspecialchars($waarde);
                            $xml .= "</$sleutel>";
                        }
                        $xml .= "</item>";
                    }
                    $xml .= "</items>";
                }

                unset($items_array);
                unset($items);
                unset($qr_items);
            }

            unset($prim_key);
            $xml.="</record>\n";
            file_put_contents($file, $xml, FILE_APPEND);
        }

    }
    //close the root element
    $xmlend = "</$root_element>";
    file_put_contents($file, $xmlend, FILE_APPEND);
    //echo $xml;
/*
    $logDir = "/www/libis/web/lias_html/ca_crkc/crkc_tools/log/";
    $file_handle = fopen($logDir.$table_name.".xml", "w"); //open file for writing
    if (!file_handle) {
        echo "Hier loopt het mis !!!!!!";
        die('file open error');
    }
    fwrite($file_handle, $xml); //write XML content to file
    fclose($file_handle); //close file
 *
 */

    unset($metadata);
    unset($xml);
    unset($file);
}