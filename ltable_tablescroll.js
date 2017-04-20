function disablescrollctrl(sTbl, bStatus)
{
	$('__'+sTbl+'_left').disabled=bStatus;
	$('__'+sTbl+'_right').disabled=bStatus;
	$('__'+sTbl+'_up').disabled=bStatus;
	$('__'+sTbl+'_down').disabled=bStatus;
	$('__'+sTbl+'_bleft').disabled=bStatus;
	$('__'+sTbl+'_bright').disabled=bStatus;
	$('__'+sTbl+'_bup').disabled=bStatus;
	$('__'+sTbl+'_bdown').disabled=bStatus;
}
function ira(sTbl, col, row)
{
	disablescrollctrl(sTbl, true);
	var tbl=$(sTbl);
	var mincol=parseInt($('__'+sTbl+'_mc').value);
	var minrow=parseInt($('__'+sTbl+'_mr').value);
	var maxrow=tbl.rows.length;
	var maxcol=tbl.rows[0].cells.length;
	if (col > mincol)
	{
		var nx,ny;
		for (nx=mincol;nx<col;nx++)
		{
			for (ny=0; ny<maxrow; ny++) tbl.rows[ny].cells[nx].style.display='none';
		}
	}
	if (row > minrow)
	{
		var nx,ny;
		for (ny=minrow;ny<row;ny++)
		{
			for (nx=0; nx<maxcol; nx++) tbl.rows[ny].cells[nx].style.display='none';
		}
	}
	$('__'+sTbl+'_c').value = col;
	$('__'+sTbl+'_r').value = row;
	disablescrollctrl(false);
}
function volvera(sTbl, col, newcol, row, newrow)
{
	disablescrollctrl(sTbl, true);
	var tbl=$(sTbl);
	var maxrow=tbl.rows.length;
	var maxcol=tbl.rows[0].cells.length;
	var nx,ny;
	if (col != newcol)
	{
		for (nx=col-1;nx>=newcol;nx--)
		{
			for (ny=0; ny<maxrow; ny++) tbl.rows[ny].cells[nx].style.display='';
		}
	}
	if (row != newrow)
	{
		for (ny=row-1;ny>=newrow;ny--)
		{
			for (nx=0; nx<maxcol; nx++) tbl.rows[ny].cells[nx].style.display='';
		}
	}
	$('__'+sTbl+'_c').value = newcol;
	$('__'+sTbl+'_r').value = newrow;
	disablescrollctrl(false);
}
function der_largo(sTbl, cant)
{
	var col=parseInt($('__'+sTbl+'_c').value);
	var mincol=parseInt($('__'+sTbl+'_mc').value);
	var row=parseInt($('__'+sTbl+'_r').value);
	var newcol;
	newcol = col - cant;
	if (newcol < mincol) newcol = mincol;
	volvera(sTbl, col, newcol, row, row);
}
function izq_largo(sTbl, cant)
{
	var col=parseInt($('__'+sTbl+'_c').value);
	var maxcol=$(sTbl).rows[0].cells.length;
	var row=parseInt($('__'+sTbl+'_r').value);
	col = col + cant;
	if (col > maxcol) col = maxcol-1;
	ira(sTbl, col, row);
}
function arr_largo(sTbl, cant)
{
	var col=parseInt($('__'+sTbl+'_c').value);
	var minrow=parseInt($('__'+sTbl+'_mr').value);
	var row=parseInt($('__'+sTbl+'_r').value);
	var newrow;
	newrow = row - cant;
	if (newrow < minrow) newrow = minrow;
	volvera(sTbl, col, col, row, newrow);
}
function aba_largo(sTbl, cant)
{
	var col=parseInt($('__'+sTbl+'_c').value);
	var maxrow=$(sTbl).rows.length;
	var row=parseInt($('__'+sTbl+'_r').value);
	row = row + cant;
	if (row > maxrow) row = maxrow-1;
	ira(sTbl, col, row);
}
function derecha(sTbl)
{
	disablescrollctrl(sTbl, true);
	var tbl=$(sTbl);
	var col=parseInt($('__'+sTbl+'_c').value);
	var row=parseInt($('__'+sTbl+'_r').value);
	var mincol=parseInt($('__'+sTbl+'_mc').value);
	var minrow=parseInt($('__'+sTbl+'_mr').value);
	var maxrow=tbl.rows.length;
	var x;
	if (col>mincol)
	{
		col--;
		$('__'+sTbl+'_c').value = col;
		for (x=0; x<minrow; x++)
		{
			tbl.rows[x].cells[col].style.display='';
		}
		for (x=row; x<maxrow; x++)
		{
			tbl.rows[x].cells[col].style.display='';
		}
	}
	disablescrollctrl(false);
}
function izquierda(sTbl)
{
	disablescrollctrl(sTbl, true);
	var tbl=$(sTbl);
	var col=parseInt($('__'+sTbl+'_c').value);
	var x;
	var nr=tbl.rows.length;
	var maxcol=tbl.rows[0].cells.length;
	if (col<maxcol-1)
	{
		for (x=0; x<nr; x++)
		{
			tbl.rows[x].cells[col].style.display='none';
		}
		col++;
		$('__'+sTbl+'_c').value = col;
	}
	disablescrollctrl(sTbl, false);
}
function abajo(sTbl)
{
	disablescrollctrl(sTbl, true);
	var tbl=$(sTbl);
	var row=parseInt($('__'+sTbl+'_r').value);
	var x;
	var nr=tbl.rows.length;
	if (row<nr-1)
	{
		var maxcol=tbl.rows[row].cells.length;
		for (x=0; x<maxcol; x++)
		{
			tbl.rows[row].cells[x].style.display='none';
		}
		row++;
		$('__'+sTbl+'_r').value = row;
	}
	disablescrollctrl(sTbl, false);
}
function arriba(sTbl)
{
	disablescrollctrl(sTbl, true);
	var tbl=$(sTbl);
	var row=parseInt($('__'+sTbl+'_r').value);
	var col=parseInt($('__'+sTbl+'_c').value);
	var mincol=parseInt($('__'+sTbl+'_mc').value);
	var minrow=parseInt($('__'+sTbl+'_mr').value);
	var x;
	if (row>minrow)
	{
		row--;
		$('__'+sTbl+'_r').value = row;
		var maxcol=tbl.rows[row].cells.length;
		for (x=0; x<mincol; x++) {tbl.rows[row].cells[x].style.display='';}
		for (x=col; x<maxcol; x++) {tbl.rows[row].cells[x].style.display='';}		
	}
	disablescrollctrl(sTbl, false);
}