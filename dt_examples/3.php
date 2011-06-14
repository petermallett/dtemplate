<?php
    include('../class.DTemplate.php');

    $tpl = new DTemplate();

    $tpl->define_templates(array('body' => '3.tpl'));

    $d = date('Y-m-d');

    $tpl->define_block('table', 'body');   //body is the parent of table
    $tpl->define_block('row', 'table');    //table is the parent of row

    $TABLEX = 5;
    $ROWX = 10;
    for ($t = 1; $t <= $TABLEX; $t++)
    {
        $tpl->clear_output('row');  //start a new row
        for ($r = 1; $r <= $ROWX; $r++)
        {
            $tpl->assign(array(
                        'A' => "A in row $r in table $t",
                        'B' => "B in row $r in table $t",
                        'C' => "C in row $r in table $t",
                        'D' => "D in row $r in table $t"
                    ));
            $tpl->process('row');
        }
        $tpl->process('table');
    }

    $tpl->assign(array(
            'DOCTITLE' => 'nested dynamic block example',
            'DATE'    => $d,
            'TABLEX' => $TABLEX,
            'ROWX' => $ROWX
        ));

    $tpl->process('body');
    
    $tpl->DPrint('body');
?>