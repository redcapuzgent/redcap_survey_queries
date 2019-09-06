<?php

namespace uzgent\SurveyQueriesClass;

// Declare your module class, which must extend AbstractExternalModule
use REDCap;

require_once __DIR__."/DataResolutionDAO.php";

class SurveyQueriesClass extends \ExternalModules\AbstractExternalModule {

    public function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
        global $rc_connection;
        $resolutionDao = new \DataResolutionDAO($rc_connection);
        echo "<script>";
        foreach ($this->getMetadata($project_id, $instrument) as $field) {
            $field = $field['field_name'];
            if ($resolutionDao->fieldHasComments($project_id, $record, $field, 1))
            {
                $value = $resolutionDao->getFieldComment($project_id, $record, $field, 1);
                echo 'document.getElementById("label-dob").appendChild(document.createTextNode(" '.$value.'"));';

                //echo "<input type='text' value='".$value."'>";
            }

        }
        echo "</script>";

        //Foreach field check if there is a query.

    }

}
