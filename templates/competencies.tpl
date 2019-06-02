<div class="">
	{{if $competencies}}
		{{foreach $competencies as $competencie}}
			{{include file="addon/competence/templates/competencie.tpl"}}
		{{/foreach}}
	{{else}}
		Nenhuma competencia adicionada
	{{/if}}
</div>

<!--<br>

<div name="add-competencie" style="display: {{$show}};" >
	<form id="add-competence" name="add-competence" method="post" >
		<a id="event-submit" class="btn" title="{{$add}}" onclick="document.getElementById('add-competence').submit();">
			{{$add}}
		</a>
	</form>
	{{$q}}
</div>-->

<br>

<div class="add-competencie" style="display: {{$show}};" >
	<a class="btn" href="{{$addLink}}" title="{{$add}}">{{$add}}</a>
</div>
