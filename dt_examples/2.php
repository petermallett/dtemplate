<?php
    include('../class.DTemplate.php');

    $tpl = new DTemplate();

    $tpl->define_templates(array('body' => '2.tpl'));

    $d = date('Y-m-d');

    $tpl->define_block('row', 'body');

    $rows = 10;
    for ($x = 1; $x <= $rows; $x++)
    {
        $tpl->assign(array(
                    'A' => "A in row $x",
                    'B' => "B in row $x",
                    'C' => "C in row $x",
                    'D' => "D in row $x"
                ));
        $tpl->process('row');
    }

    $tpl->assign(array(
            'DOCTITLE'  => 'dynamic block example',
            'DATE'      => $d,
            'ROWS'      => $rows
        ));

    $tpl->process('body');
    
    $tpl->DPrint('body');
?>