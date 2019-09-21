<?php
/*
 Track class.
 Purpose is to provide a means for tracking progress on a quiz-type application.
 The tracking database table currently has a user_id and a tracking_data field.
 The public functions here allow an app to save and retrieve the tracking_data
   for a specified user.

 Currently, the tracking_data field is a varchar(255), with the intent for
   data to be stored in a JSON string. Perhaps when I get around to upgrading
   the mysql on my server, I'll convert the field to a json field.
*/
class Track {
  public function __construct() {
    global $dbh;
    $this->_dbh = $dbh;
  }


  public function set($user_id, $tracking_data) {
    if ($this->isNewTrackingRecord($user_id)) {
      $result = $this->addTrackingRecord($user_id, $tracking_data);
    } else {
      $result = $this->updateTrackingRecord($user_id, $tracking_data);
    }
    return $result;
  }

  public function getTrack($user_id) {
    $sql = "SELECT tracking_data FROM tracking WHERE user_id=:user_id";
    $stmt = $this->_dbh->prepare($sql);
    $stmt->bindColumn(1, $tracking_data);
    $params = array(':user_id' => $user_id);
    if ($stmt->execute($params)) {
      $stmt->fetch();
    }
    return array('tracking_data' => $tracking_data);
  }


  /*
    private functions start here
  */
  private function isNewTrackingRecord($user_id) {
    $sql = "SELECT tracking_data FROM tracking WHERE user_id=:user_id";
    $stmt = $this->_dbh->prepare($sql);
    $stmt->bindColumn(1, $tracking_data);
    $params = array(':user_id' => $user_id);
    $count = 0;
    if ($stmt->execute($params)) {
      $count = $stmt->rowCount();
      $data = array('count' => $count);
    } else {
      $data = array('status' => 'problem with stmt->execute');
    }
    return $count === 0;
  }

  private function addTrackingRecord($user_id, $tracking_data) {
    $params = array(':user_id' => $user_id, ':tracking_data' => $tracking_data);
    $sql = "INSERT INTO tracking (user_id, tracking_data) VALUES (:user_id, :tracking_data)";
    $insert_stmt = $this->_dbh->prepare($sql);
    $result = $insert_stmt->execute($params);
    return $result;
  }

  private function updateTrackingRecord($user_id, $tracking_data) {
    $params = array(':user_id' => $user_id, ':data' => $tracking_data);
    $sql = "UPDATE tracking SET tracking_data=:tracking_data WHERE user_id=:user_id";
    $update_stmt = $this->_dbh->prepare($sql);
    $result = $update_stmt->execute($params);
    return $result;
  }

}
