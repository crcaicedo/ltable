<?php
class lt_dialog
{
	public $id='dialog-form', $autoOpen=false, $height=300, $width=500, $modal=true, $content_fn='';
	function render(lt_form $fo)
	{
		$fo->div($id, 3);
		$content_fn = $this->content_fn;
		if ($content_fn != '') $content_fn($fo);
		$fo->divx();
	}
}
?>