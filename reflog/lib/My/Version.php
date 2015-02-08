<?php
class My_Version extends My_Dbobject{

    
    function getList(){
        $st = $this->_db->query("select * from version order by dir");
        $dataList = array();
        while($row = $st->fetch(PDO::FETCH_ASSOC)){
            $row['createdate'] = date('Y-m-d',$row['createdate']);
            $dataList[] = $row;
        }
        return $dataList;
    }
    
    function save($data){
        $this->_db->beginTransaction();
        $stmt = $this->_prepare('select * from version where dir = :dir' );
        $stmt->bindValue(':dir', $data['dir'], PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        if($row){
            $stmt = $this->_prepare("update version set description = :description where dir = :dir");
            $stmt->bindValue(':dir', $data['dir'], PDO::PARAM_STR);
            $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        }else{
            $stmt = $this->_prepare("insert into version(dir,createdate,description)values(:dir,:createdate,:description)");
            $stmt->bindValue(':dir', $data['dir'], PDO::PARAM_STR);
            $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindValue(':createdate', strtotime($data['createdate']), PDO::PARAM_INT);
        }
        $stmt->execute();
        $this->_db->commit();
        return $this->checkError($stmt);
    }
    
    function delete($dir){
        $stmt = $this->_prepare("delete from version where dir = :dir");
        $stmt->bindValue(':dir', $dir, PDO::PARAM_STR);
        $stmt->execute();
        return $this->checkError($stmt);
    }

}
