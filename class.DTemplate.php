<?php
// +---------------------------------------------------------------------------+
// | DTemplate version 1.2                                                     |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Peter Mallett                                         | 
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions are    |
// | met:                                                                      |
// |                                                                           |
// |    * Redistributions of source code must retain the above copyright       |
// |      notice, this list of conditions and the following disclaimer.        |
// |    * Redistributions in binary form must reproduce the above              |
// |      copyright notice, this list of conditions and the following          |
// |      disclaimer in the documentation and/or other materials               |
// |      provided with the distribution.                                      |
// |    * Neither the name of the software nor the names of its contributors   |
// |      may be used to endorse or promote products derived from this         |
// |      software without specific prior written permission.                  |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED |
// | TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A           |
// | PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER  |
// | OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,  |
// | EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,       |
// | PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR        |
// | PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF    |
// | LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING      |
// | NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS        |
// | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.              |
// +---------------------------------------------------------------------------+
// | Author: Peter Mallett <pmallett@desolatewaste.com>                        |
// +---------------------------------------------------------------------------+
define('DT_VERSION', '1.2');
define('DT_COPYRIGHT', 'Copyright (c) 2004, Peter Mallett');
class DTemplate
{
// Settings vars
    var $FIELD_START    = '{';       	//format of field delimiters eg: "{fieldname}"
    var $FIELD_END      = '}';
    var $BLOCK_START  = '<block:%s>';   //format of sub-block delimiters in template files
    var $BLOCK_END    = '</block:%s>';
    var $COMMENT_START  = '<!-- ';      //comment format for template output
    var $COMMENT_END    = ' -->';
// Program vars
    var $FILENAME   = array();  //array of template file names;                 $FILENAME[$tHandle]
    var $BLOCK      = array();  //array of unprocessed template block contents; $BLOCK[$blockHandle][0..n]
    var $BLOCK_INFO = array();  //array of block type info                      $BLOCK_INFO[$blockHandle][0..n] == 'code' || 'ref'
    var $VALUE      = array();  //array of field values;                        $VALUE[$fieldName]
    var $OUTPUT     = array();  //array of processed template output;           $OUTPUT[$blockHandle]
    
    var $SUBBLOCKS  = array();  //array of lists of blocks by parent handle;    $SUBBLOCKS[$parentHandle] == array
    var $PARENT     = array();  //array of blocks' parent handle;               $PARENT[$blockHandle] = $parentHandle
    
    var $TEMPLATE_DIR = '';     //path to template files
    var $fCopyrightOutput = 0;  //switch for copyright output done

/*-------------------------------------------------------------
  Begin Interface Functions */

//-------------------------------------------------------------
// Constructor, sets template dir
    function DTemplate($templateDir = '.')
    {
        $this->set_template_dir($templateDir);
    }

//-------------------------------------------------------------
    function set_template_dir($dir)
    {
        if (is_dir($dir))
        {
            $this->TEMPLATE_DIR = $dir;
        } else {
            $this->TEMPLATE_DIR = '';
            $this->error("Specified template direcotry [$dir] is not a directory");
        }
    } // end set_template_dir()


//-------------------------------------------------------------
// setup relationship between $tHandle and template file names
// filelist should be an array of file names referenced by $tHandle's
    function define_templates($filelist)
    {
        if (!is_array($filelist))
        {
            $this->error("define_templates() failure: \$filelist is not an array.", 1);
        }

        foreach ($filelist as $tHandle => $fileName)
        {
            $this->define_template($tHandle, $fileName);
        }
    }

//-------------------------------------------------------------
// setup relationship between ONE $tHandle and ONE template filename
    function define_template($tHandle, $filename)
    {
        $this->FILENAME[$tHandle] = $filename;
        $this->PARENT[$tHandle] = NULL;
    }

//-------------------------------------------------------------
// setup relationship between sub-block name and the parent block
    function define_block($blockHandle, $parentHandle)
    {
        $this->PARENT[$blockHandle] = $parentHandle;
        $this->SUBBLOCKS[$parentHandle][] = $blockHandle;
    }

//-------------------------------------------------------------
// Assign values for template fields.
// assign() has dual functionality:
//
// assign(array(KEY => 'value', ...))
//      where KEY matches the name of a template field and each pair in the array is assign
//
// assign(KEY, 'value')
//  for assigning a value to one template field
//
    function assign($field_array, $single_val = '')
    {
        if (is_array($field_array))
        {
            foreach ($field_array as $key => $val)
            {
                if (!(empty($key)))
                {
                    //  can not have empty keys
                    $this->VALUE[$key] = $val;
                }
            }
        }
        else {
            // since $field_array is NOT an array, assume it's a single key
            if (!empty($field_array))
            {
                $single_key = $field_array;     //just for code clarity
                $this->VALUE[$single_key] = $single_val;
            }
        }
    } // end assign()
    
//-------------------------------------------------------------
//  Clears values set by assign()
    function clear_assign($key = NULL)
    {
        if ($key != NULL)
        {
            unset($this->VALUE[$key]);
        }
        else if (!empty($this->VALUE))
        {
            reset($this->VALUE);
            while (list($key) = each($this->VALUE))
            {
                unset($this->VALUE[$key]);
            }
        }
    }
    
//-------------------------------------------------------------
// load and process block content
    function process($blockHandle, $outHandle = NULL)
    {
        if ( (isset($this->PARENT[$blockHandle])) || (isset($this->FILENAME[$blockHandle])) )
        {
            $this->load_block($blockHandle);
            $this->process_block($blockHandle, $outHandle);
        } else {
            //$Handle is an unknown block
            $this->error("process(): '$blockHandle' is an undefined block", 1);
        }
    } // end process()
    
//-------------------------------------------------------------
// alias to process(), parse() is deprecated and may be removed
// in later versions
    function parse($blockHandle, $outHandle)
    {
        $this->process($blockHandle, $outHandle);
    }
    
//-------------------------------------------------------------
// Clears template output created by process()
    function clear_output($outHandle = NULL)
    {
        if ($outHandle != NULL)
        {
            //with sub-blocks, references to output blocks are used, so unsetting them could cause problems
            //set to empty value instead
            $this->OUTPUT[$outHandle] = '';
        }
        else if (!empty($this->OUTPUT))
        {
            reset($this->OUTPUT);
            while (list($key) = each($this->OUTPUT))
            {
                $this->OUTPUT[$key] = '';
            }
        }
    }
    
//-------------------------------------------------------------
    function DPrint($outHandle)
    {
        if( (!isset($this->OUTPUT[$outHandle])) || (empty($this->OUTPUT[$outHandle])) )
        {
            $this->error('There is no processed data for '. $outHandle);
        } else {
            echo $this->OUTPUT[$outHandle], $this->getCopyrightComment();
        }
    }

//-------------------------------------------------------------
    function fetch($outHandle)
    {
        if ( (!isset($this->OUTPUT[$outHandle])) || (empty($this->OUTPUT[$outHandle])))
        {
            $this->error('There is no processed data for '. $outHandle);
            return('');
        } else {
            $out = $this->OUTPUT[$outHandle];
            $out .= $this->getCopyrightComment();
            return($out);
        }
    }
    
/*-------------------------------------------------------------

  Begin Internal Functions */

//-------------------------------------------------------------
// check to see if template block is loaded, if not, load it
    function load_block($blockHandle)
    {
        if (!isset($this->BLOCK[$blockHandle]))
        {
            if ($this->PARENT[$blockHandle] != NULL)
            {
                $this->load_block($this->PARENT[$blockHandle]);
            }
            //ok ready to load block
            if (isset($this->FILENAME[$blockHandle]))
            {
                $this->load_template_file($blockHandle);
            } else {
                $this->load_sub_block($blockHandle);
            }
        }
    }

//-------------------------------------------------------------
// read template file into string from a file
    function load_template_file($tHandle)
    {
        if (empty($this->TEMPLATE_DIR))
        {
            $this->error('Can not load template file. TEMPLATE_DIR is not valid.', 1);
        }

        $filename = $this->TEMPLATE_DIR .'/'. $this->FILENAME[$tHandle];

        if (file_exists($filename))
        {
            if (($this->BLOCK[$tHandle][0] = file_get_contents($filename)) == FALSE)
            {
                $this->error("load_template_file() failure: [$filename] $php_errormsg", 1);
            } else {
                $this->BLOCK_INFO[$tHandle][0] = 'code';
            }
        }
        else {
            $this->error("load_template_file() failure: [$filename] does not exist", 1);
        }
    }

//-------------------------------------------------------------
// extract the code of the sub-block from the parent block
// record offset of sub-block start
// remove sub-block code from parent block
    function load_sub_block($blockHandle)
    {
        $start_tag = sprintf($this->BLOCK_START, $blockHandle);
        $start_len = strlen($start_tag);
        $end_tag = sprintf($this->BLOCK_END, $blockHandle);
        $end_len = strlen($end_tag);

        $parentHandle = $this->PARENT[$blockHandle];
        
        $found = 0;
        reset($this->BLOCK[$parentHandle]);
        while(list($key) = each($this->BLOCK[$parentHandle]))
        {
            $p_block =& $this->BLOCK[$parentHandle][$key];
            $s = strpos($p_block, $start_tag);
            if ($s !== false)
            {
                $found = 1;
                $e = strpos($p_block, $end_tag, $s);
                if ($e === false)
                {
                    $this->error("load_sub_block(): no end tag for sub-block: $blockHandle", 1);
                }
                //get code for sub-block
                $block_start = $s + $start_len;
                $block_len = $e - $block_start;
                $this->BLOCK[$blockHandle][0] = substr($p_block, $block_start, $block_len);
                $this->BLOCK_INFO[$blockHandle][0] = 'code';
        
                //remove sub-block from parent block
                $block_code_end = $e + $end_len;
                if (!isset($this->OUTPUT[$blockHandle]))
                {
                    $this->OUTPUT[$blockHandle] = '';
                }
                $temp = substr($p_block, $block_code_end);    //end of parent block (after end of sub-block)
                array_splice($this->BLOCK[$parentHandle], ($key + 1), 0, array(&$this->OUTPUT[$blockHandle], $temp));
                $this->BLOCK_INFO[$parentHandle][($key + 1)] = 'ref';
                $this->BLOCK_INFO[$parentHandle][($key + 2)] = 'code';
                $this->BLOCK[$parentHandle][$key] = substr($p_block, 0, $s);
                
                next($this->BLOCK[$parentHandle]);  //advance the array pointer since we inserted 2 new indexes
                next($this->BLOCK[$parentHandle]);
            }
        }
        
        if (!$found)
        {
            $this->error("load_sub_block(): $blockHandle not found in $parentHandle", 1);
        }
    } // end load_sub_block()
    
//-------------------------------------------------------------
//  This function is called by process() and does the field to value conversion
//  as well as inserts output from sub-blocks previously processed
    function process_block($blockHandle, $outHandle)
    {
        if (!$outHandle)
        {
            $outHandle = $blockHandle;
        }
        if (!isset($this->OUTPUT[$outHandle]))
        {
            $this->OUTPUT[$outHandle] = '';
        }
        reset($this->BLOCK[$blockHandle]);
        while(list($key) = each($this->BLOCK[$blockHandle]))
        {
            if ($this->BLOCK_INFO[$blockHandle][$key] == 'code')
            {
                $output = $this->BLOCK[$blockHandle][$key]; //copy code
                //fill in field values
                foreach($this->VALUE as $key => $val)
                {
                    $key = '{'. $key .'}';
                    $output = str_replace($key, $val, $output);
                }
                $this->OUTPUT[$outHandle] .= $output;
            } else {
                $this->OUTPUT[$outHandle] .= $this->BLOCK[$blockHandle][$key]; //copy sub-block output from reference
            }
        }
    } // end process_block()


//-------------------------------------------------------------
    function printCopyright()
    {
        if (!$this->fCopyrightOutput)
        {
            print("DTemplate version ". DT_VERSION ." ". DT_COPYRIGHT ."<BR><BR>\n");
            $this->fCopyrightOutput = 1;
        }
    }


//-------------------------------------------------------------
    function getCopyrightComment()
    {
        if (!$this->fCopyrightOutput)
        {
            $out = $this->COMMENT_START ."DTemplate version ". DT_VERSION ." ". DT_COPYRIGHT ." ". $this->COMMENT_END ."\n";
            $this->fCopyrightOutput = 1;
        } else {
            $out = '';
        }
        return($out);
    }



//-------------------------------------------------------------
    function warning($errorMsg)
    {
        print($this->getCopyrightComment());
        print("[DTemplate] Warning: $errorMsg \n");
    }


//-------------------------------------------------------------
    function error($errorMsg, $die = 0)
    {
        $this->printCopyright();
        print("[DTemplate] ERROR: $errorMsg \n");
        if ($die != 0)
        {
            exit($die);
        }
    }


} // class DTemplate

?>