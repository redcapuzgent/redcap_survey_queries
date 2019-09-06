<?php

/**
 * Description of DataValue
 *
 * @author lveeckha
 */
class DataResolutionDAO {
        /**
     * @var resource
     */
    private $conn;

    /**
     *
     * @param resource $conn
     */
    public function __construct($conn)
    {
        if ($conn === null) throw new Exception("Connection cannot be null");
        $this->conn = $conn;
    }
    
    public function fieldHasComments($projectid, $record, $field, $instanceid)
    {
        $sql = "SELECT count(*) as count FROM redcap_data_quality_status WHERE project_id=? AND field_name=? AND instance = ? AND record = ?";
        $prepared = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($prepared, "dsds", $projectid, $field, $instanceid, $record);
        mysqli_stmt_execute($prepared);
        mysqli_stmt_bind_result($prepared, $count);
        mysqli_stmt_fetch($prepared);
        if (mysqli_stmt_error($prepared) != "")
        {
            throw new Exception("Unable to execute query " . mysqli_stmt_error($prepared) . " $sql");
        }
        return $count > 0;

    }

    public function getFieldComment($project_id, $record, $field, $instanceid)
    {
        $comment = "";
        $sql = "SELECT comment as count FROM redcap_data_quality_resolutions INNER JOIN redcap_data_quality_status ON redcap_data_quality_status.status_id = redcap_data_quality_resolutions.status_id  WHERE project_id=? AND field_name=? AND instance = ? AND record = ?";
        $prepared = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($prepared, "dsds", $project_id, $field, $instanceid, $record);
        mysqli_stmt_execute($prepared);
        mysqli_stmt_bind_result($prepared, $comment );
        mysqli_stmt_fetch($prepared);
        return $comment;
    }
    
    /**
     * 
     * @param int $projectid
     * @param string $record
     * @param string $field
     * @param int $instanceid
     * @param string $userid
     */
    public function markFieldAsVerified($projectid, $record, $field, $instanceid, $userid, $eventid)
    {
        $dr_status = "VERIFIED";
        $sql = "insert into redcap_data_quality_status (non_rule, project_id, record, event_id, field_name, query_status, instance)
                        values (1, ?, ?, ?, ?, ?, ?)
                        on duplicate key update query_status = ?, status_id = LAST_INSERT_ID(status_id)";
        $prepared_status = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($prepared_status, "dsdssds",      $projectid, $record, $eventid, $field,    $dr_status,   $instanceid, 
                                                            $dr_status);
        mysqli_stmt_execute($prepared_status);
        if (mysqli_stmt_error($prepared_status) != "")
        {
            throw new Exception("Unable to execute query " . mysqli_stmt_error($prepared_status) . " $sql");
        }
        $status_id = mysqli_insert_id($this->conn);
        $userInitiator = User::getUserInfo($userid);

        $sql = "insert into redcap_data_quality_resolutions (status_id, ts, user_id, response_requested,
                                        response, comment, current_query_status, upload_doc_id)
                                        VALUES (?, ?, ?,
                                        0, NULL, NULL, ?, NULL)"; //UPLOADDOC_ID
        $prepared_resolution = mysqli_prepare($this->conn, $sql);
        $now = NOW;
        mysqli_stmt_bind_param($prepared_resolution, "dsds", $status_id, $now, $userInitiator['ui_id'], $dr_status);
        mysqli_execute($prepared_resolution);
        if (mysqli_stmt_error($prepared_resolution) != "")
        {
            throw new Exception("Unable to execute query " . mysqli_stmt_error($prepared) . " $sql");
        }
    }
    
}
