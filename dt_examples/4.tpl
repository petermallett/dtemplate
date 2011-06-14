<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>{DOCTITLE}</title>
</head>

<body>
<p>A more advanced example of dynamic blocks.</p>
<p>{DATE}</p>
<block:table>
<table border="1">
  <block:rowset>
  <block:row1>
  <tr>
    <td>row type1</td>
    <td>{A}</td>
    <td>{B}</td>
    <td>{C}</td>
  </tr>
  </block:row1>
  <block:row2>
  <tr>
    <td>row type2</td>
    <td colspan="3">{A}</td>
  </tr>
  </block:row2>
  </block:rowset>
</table>
<div><br/>
</div>
</block:table>
</body>
</html>
