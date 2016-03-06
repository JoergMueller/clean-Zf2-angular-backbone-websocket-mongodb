<?
$filter = new \Zend\Filter\Word\UnderscoreToSeparator('/');
$documentId = $document->getId();
?>

<div class="widgetHolder margin-0" data-document="<?=$documentId?>"  data-path="<?=$documentId?>/slider" data-valid="">
<?
$widgets = $dm->createQueryBuilder('Metacope\Mcedit\Model\WidgetModel')
			->field('parent')->references($document)
			->field('anker')->equals($documentId."/slider")
			->sort('datecreate', 'asc')
			->getQuery()->execute();
foreach($widgets as $widget) {

	include WIDGET . DIRECTORY_SEPARATOR . $filter->filter($widget->getType()) .'.phtml';
}
?></div>

<div class="page-content">
    <div class="container">
        <div class="row">
			
			<div class="col-md-4 col-sm-6">
				<div class="widgetHolder" data-document="<?=$documentId?>"  data-path="<?=$documentId?>/col-md-4/1" data-valid="">
				<?
				$widgets = $dm->createQueryBuilder('Metacope\Mcedit\Model\WidgetModel')
							->field('parent')->references($document)
							->field('anker')->equals($documentId."/col-md-4/1")
							->sort('datecreate', 'asc')
							->getQuery()->execute();
				foreach($widgets as $widget) {
					include WIDGET . DIRECTORY_SEPARATOR . $filter->filter($widget->getType()) .'.phtml';
				}
				?></div>
			</div>

			<div class="col-md-4 col-sm-6 clearfix">
				<div class="widgetHolder" data-document="<?=$documentId?>"  data-path="<?=$documentId?>/col-md-4/2" data-valid="">
				<?
				$widgets = $dm->createQueryBuilder('Metacope\Mcedit\Model\WidgetModel')
							->field('parent')->references($document)
							->field('anker')->equals($documentId."/col-md-4/2")
							->sort('datecreate', 'asc')
							->getQuery()->execute();
				foreach($widgets as $widget) {
					include WIDGET . DIRECTORY_SEPARATOR . $filter->filter($widget->getType()) .'.phtml';
				}
				?></div>
			</div>

			<div class="col-md-4 col-sm-12">
				<div class="widgetHolder" data-document="<?=$documentId?>"  data-path="<?=$documentId?>/col-md-4/3" data-valid="">
				<?
				$widgets = $dm->createQueryBuilder('Metacope\Mcedit\Model\WidgetModel')
							->field('parent')->references($document)
							->field('anker')->equals($documentId."/col-md-4/3")
							->sort('datecreate', 'asc')
							->getQuery()->execute();
				foreach($widgets as $widget) {
					include WIDGET . DIRECTORY_SEPARATOR . $filter->filter($widget->getType()) .'.phtml';
				}
				?></div>
			</div>



        </div><!-- .row end -->
    </div><!-- .container end -->
</div><!-- .page-content end -->
