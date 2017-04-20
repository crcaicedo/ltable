<?php
function sb($bool)
{
	return $bool ? 'true':'false';
}

class lt_grid_column
{
	public $id='', $name='', $field='', $width=0, $minWidth=0, $maxWidth=0, $cssClass=''; 
	public $editor='Slick.Editors.Text', $validator='', $formatter='', $resizable=true;
	
	function __construct($id='', $name='', $field='', $width=0, $minWidth=0, $maxWidth=0, $cssClass='',
			$editor='Slick.Editors.Text', $validator='', $formatter='', $resizable=true)
	{
		$this->id = $id;
		$this->name = $name;
		$this->field = $field;
		$this->width = $width;
		$this->minWidth = $minWidth;
		$this->maxWidth = $maxWidth;
		$this->cssClass = $cssClass;
		$this->editor = $editor;
		$this->validator = $validator;
		$this->formatter = $formatter;
		$this->resizable = $resizable;
	}
}

class lt_grid
{
	public $cols = array(), $cols_c = 0, $cells=array(), $rows_c=0, $width=0, $height=0;
	public $div_id = '', $editable = false, $enableAddRow = false, $enableCellNavigation = true;
	public $asyncEditorLoading = false, $autoEdit = false;

	public function addColumn($id='', $name='', $field='', $width=0, $minWidth=0, $maxWidth=0, $cssClass='',
			$editor='Slick.Editors.Text', $validator='', $formatter='', $resizable=true)
	{
		$this->cols[$this->cols_c++] = new lt_grid_column($id, $name, $field, $width, $minWidth, $maxWidth, 
			$cssClass, $editor, $validator, $formatter, $resizable); 
	}
	public function fromObjArray(array &$oa)
	{
		$this->rows_c = 0;
		foreach ($oa as $ox)
		{
			$this->cells[$this->rows_c++] = clone $ox;
		}
	}
	public function fromQuery(lt_form $fo, $query)
	{
		$qa = new myquery($fo, $query, 'LTGRID_FROMQUERY-1', false);
		if ($qa->isok) $this->fromObjArray($qa->a);
	}
	
	public function render(lt_form $fo)
	{
		$fo->divc('', $this->div_id, LT_ALIGN_DEFAULT, '', 
			sprintf("width:%dpx;height:%dpx;", $this->width, $this->height));
		$fo->buf .= "<script src=\"jquery.min.js\"></script>
		<script src=\"jquery-ui.min.js\"></script>
		<script src=\"jquery_nc.js\"></script>
		<script src=\"slickgrid/lib/jquery.event.drag-2.2.js\"></script>
		<script src=\"slickgrid/slick.core.js\"></script>
		<script src=\"slickgrid/plugins/slick.cellrangedecorator.js\"></script>
		<script src=\"slickgrid/plugins/slick.cellrangeselector.js\"></script>
		<script src=\"slickgrid/plugins/slick.cellselectionmodel.js\"></script>
		<script src=\"slickgrid/slick.formatters.js\"></script>
		<script src=\"slickgrid/slick.editors.js\"></script>
		<script src=\"slickgrid/slick.grid.js\"></script>";
		
		// render options
		$fo->buf .= sprintf("<script>var grid; var data=[]; var options={editable:%s, enableAddRow:%s, ".
				"enableCellNavigation:%s, asyncEditorLoading:%s, autoEdit:%s};\n",
				sb($this->editable), sb($this->enableAddRow), sb($this->enableCellNavigation),
				sb($this->asyncEditorLoading), sb($this->autoEdit));
				
		if ($this->cols_c > 0)
		{ 
			$scol = '';
			$fo->buf .= "var columns=[";
			// render columns
			foreach ($this->cols as $c)
			{
				$cssClass = $validator = $minw = $maxw = $fmtr = $resz = '';
				if ($c->cssClass != '') $cssClass = sprintf(", cssClass: \"%s\"", $c->cssClass);
				if ($c->validator != '') $validator = sprintf(", validator: \"%s\"", $c->validator);
				if ($c->minWidth != '') $minw = sprintf(", minWidth: \"%s\"", $c->minWidth);
				if ($c->maxWidth != '') $maxw = sprintf(", maxWidth: \"%s\"", $c->maxWidth);
				if ($c->formatter != '') $fmtr = sprintf(", formatter: %s", $c->formatter);
				if (!$c->resizable) $resz = ',resizable:false';
				$scol .= sprintf(",\n{id: \"%s\", name: \"%s\", field: \"%s\", width: %d, editor:%s%s%s%s%s%s%s}",
					$c->id, $c->name, $c->field, $c->width, $c->editor, $cssClass, $validator, 
					$minw, $maxw, $fmtr, $resz);
			}
			$fo->buf .= substr($scol, 1)."];\n";
		}
		
		$fo->buf .= "\$j(function () {\n";
		// inicializando data
		///$fo->buf .= sprintf(" for (var i = 0; i < %d; i++) { var d = (data[i] = {}); }\n", 
		///	$this->cols_c);
		
		// asignando data
		for ($jj = 0; $jj < $this->rows_c; $jj++)
		{
			$sd = '';
			$fo->buf .= sprintf("data[%d] = {", $jj);
			for ($ii = 0; $ii < $this->cols_c; $ii++)
			{ 
				$n = $this->cols[$ii]->field;
				$sd .= sprintf(",\"%s\": \"%s\" ",  
						$this->cols[$ii]->field, $this->cells[$jj]->$n);
			}
			$fo->buf .= substr($sd, 1)."};\n";
		}
			
		$fo->buf .= sprintf("\ngrid = new Slick.Grid(\"#%s\", data, columns, options); ", $this->div_id);
		$fo->buf .= "grid.setSelectionModel(new Slick.CellSelectionModel()); ";
		/*grid.onAddNewRow.subscribe(function (e, args) {
		var item = args.item;
		grid.invalidateRow(data.length);
		data.push(item);
		grid.updateRowCount();
		grid.render();
		})*/
		$fo->buf .= "});</script>";
	}
	function __construct($div_id, $width, $height, $editable=false, $enableAddRow=false, $enableCellNavigation=true,
			$asyncEditorLoading=false, $autoEdit=false)
	{
		$this->div_id = $div_id;
		$this->width = $width;
		$this->height = $height;
		$this->editable = $editable;
		$this->enableAddRow = $enableAddRow;
		$this->enableCellNavigation = $enableCellNavigation;
		$this->asyncEditorLoading = $asyncEditorLoading;
		$this->autoEdit = $autoEdit;
	}
}
?>