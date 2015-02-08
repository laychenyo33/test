<?php
class My_Operation extends My_Dbobject{
    
    function getList(){
        $st = $this->_db->query("select * from operation order by createdate desc ");
        $dataList = array();
        while($row = $st->fetch(PDO::FETCH_ASSOC)){
            $row['createdate'] = date('Y-m-d H:i:s',$row['createdate']);
            $row['modifydate'] = date('Y-m-d H:i:s',$row['modifydate']);
            $dataList[] = $row;
        }
        return $dataList;
    }
    
    function save($data){
        $ts = time();
        $this->_db->beginTransaction();
        if($data['id']){
            $stmt = $this->_prepare("update operation set description = :description,modifydate = :modify where id = :id");
            $stmt->bindValue(':modify', $ts, PDO::PARAM_INT);
            $stmt->bindValue(':description', trim($data['description']), PDO::PARAM_STR);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
        }else{
            $stmt = $this->_prepare("insert into operation(createdate,modifydate,operator,description)values(:create,:modify,:operator,:description)");
            $stmt->bindValue(':create', $ts, PDO::PARAM_INT);
            $stmt->bindValue(':modify', $ts, PDO::PARAM_INT);
            $stmt->bindValue(':operator', $data['operator'], PDO::PARAM_STR);
            $stmt->bindValue(':description', trim($data['description']), PDO::PARAM_STR);
        }
        $stmt->execute();
        $this->_db->commit();
        return $this->checkError($stmt);
    }
}
