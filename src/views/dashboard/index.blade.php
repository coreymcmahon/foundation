@extends('orchestra/foundation::layout.main')

<?php

use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\View; ?>

@section('content')

<div class="row">
	<?php if (count($panes) > 0) :
	
	$panes->add('mini-profile', '<')->title('Mini Profile')
		->attributes(array('class' => 'three columns widget'))
		->content(View::make('orchestra/foundation::layout.widgets.miniprofile'));

	foreach ($panes as $id => $pane) : ?>
		<div<?php echo HTML::attributes(HTML::decorate($pane->attributes, array('class' => 'panel'))); ?>>
		<?php if ( ! empty($pane->html)) :
			echo $pane->html; 
		else : ?>
			<div class="panel-heading"><?php echo $pane->title; ?></div>
			<?php echo $pane->content; ?>
		<?php endif; ?>
		</div>
	<?php endforeach;
	else : ?>
	<div class="jumbotron">
		<h2>Welcome to your new Orchestra Platform site!</h2>
		<p>
			If you need help getting started, check out our documentation on First Steps with Orchestra Platform. 
			If you’d rather dive right in, here are a few things most people do first when they set up a new Orchestra Platform site. 
		</p>
	</div>
	<?php endif; ?>
</div>

@stop
