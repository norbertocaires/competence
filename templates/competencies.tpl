<div class="">
	{{if $competencies}}
		{{foreach $competencies as $competencie}}
			{{include file="addon/competence/templates/competencie.tpl"}}
		{{/foreach}}
	{{else}}
		<b>No added competencie</b>
	{{/if}}
</div>

<br>

<div class="add-competencie" style="display: {{$show}};" >
	<a class="btn" href="{{$addLink}}" title="{{$add}}">{{$add}}</a>
</div>
