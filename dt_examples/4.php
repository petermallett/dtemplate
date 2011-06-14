<?php
    include('../class.DTemplate.php');

    $tpl = new DTemplate();

    $tpl->define_templates(array('body' => '4.tpl'));

    $d = date('Y-m-d');

    $tpl->define_block('table', 'body');
    $tpl->define_block('rowset', 'table');
    $tpl->define_block('row1', 'rowset');
    $tpl->define_block('row2', 'rowset');

    $table_x = 2;
    $rowset_x = 4;
    for ($t = 1; $t <= $table_x; $t++)
    {
        $row1_x = 1;
        $row2_x = 3;

        $tpl->clear_output('rowset');
        for ($rs = 1; $rs <= $rowset_x; $rs++)
        {
            $tpl->clear_output('row1');
            for ($r1 = 1; $r1 <= $row1_x; $r1++)
            {
                $tpl->assign(array(
                        'A' => "A in a row1 in rowset $rs in table $t",
                        'B' => "B in a row1 in rowset $rs in table $t",
                        'C' => "C in a row1 in rowset $rs in table $t",
                        'D' => "D in a row1 in rowset $rs in table $t"
                    ));
                $tpl->process('row1');
            }
            $tpl->clear_output('row2');
            for ($r2 = 1; $r2 <= $row2_x; $r2++)
            {
                $tpl->assign(array(
                        'A' => "A in a row2 in rowset $rs in table $t"
                    ));
                $tpl->process('row2');
            }
            $tpl->process('rowset');
            $row1_x++;
            $row2_x--;
        }
        $tpl->process('table');
    }

    $tpl->assign(array(
            'DOCTITLE' => 'Advanced nested dynamic blocks',
            'DATE'    => $d
        ));

    $tpl->process('body');
    
    $tpl->DPrint('body');
?>