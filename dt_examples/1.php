<?php
    include('../class.DTemplate.php');

    $tpl = new DTemplate();

    $tpl->define_template('body', '1.tpl');

    $d = date('Y-m-d');

    $tpl->assign(array(
            'DOCTITLE'  => 'this is the title',
            'NAME'      => 'this is the name',
            'DATE'      => $d
        ));

    $tpl->process('body');
    
    $tpl->DPrint('body');
?>