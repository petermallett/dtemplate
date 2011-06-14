<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>{DOCTITLE}</title>
</head>

<body>
<p>Template with a nested dynamic block.</p>
<p>{DATE}</p>
<block:table>
<table border="1">
  <block:row>
  <tr>
    <td>{A}</td>
    <td>{B}</td>
    <td>{C}</td>
    <td>{D}</td>
  </tr>
  </block:row>
</table>
<div><br/>
</div>
</block:table>
<p>Dynamic block 'table' parsed {TABLEX} times.<br />
  Dynamic block 'row' parsed {ROWX} times per 'table'.</p>
</body>
</html>
