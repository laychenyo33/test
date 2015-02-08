<?php
class My_Router extends GoEz_Router{
    /**
     * 解析網址
     *
     * 解析下列格式網址：
     *
     * <code>
     * http://xxxxx/basedir/controller/action
     * </code>
     *
     */
    protected function _parseUrl()
    {
        $baseDir = $this->_request->getBaseUrl();
        $currDir = str_replace('index.php', '', $_SERVER['REQUEST_URI']);
        if (false !== strpos($currDir, '?')) {
            $currDir = str_replace(substr($currDir, strpos($currDir, '?')), '', $currDir);
        }

        $pattern = '/' . preg_quote($baseDir, '/') . '\/*(.*)$/';
        preg_match($pattern, $currDir, $matches);
        if (empty($matches)) { // 如果是根目錄
            $matches = array('', ltrim($currDir, '/'));
        }
        $tickets = isset($matches[1]) ? explode('/', $matches[1]) : array ('', '');
        $this->_controller = ($tickets[0]) ? strtolower($tickets[0]) : 'index';
        $this->_action = (isset($tickets[1]) && $tickets[1]) ? strtolower($tickets[1]) : 'index';
        $t=0;
        for($i=1;$i<count($tickets);$i++){
            if($i==1){
                if(strlen($tickets[$i])<2)
                    $t++;
                else
                    continue;
            }else{
                $t++;
                if($t%2==0 && isset($tickets[$i])){
                    $this->_request->setParam($tickets[$i-1], $tickets[$i]);
                }
            }
        }
    }
}
