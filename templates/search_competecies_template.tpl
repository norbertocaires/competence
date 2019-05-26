
{{include file="section_title.tpl"}}

{{$tab_str}}

<div id="viewcontact_wrapper-{{$id}}">
{{foreach $competencies as $competencie}}
    <div>Título: {{$competencie.name}}</div>
    <div>Descrição: {{$competencie.statement}}</div>
    <div>Usuario: <a title="{{$edit}}" href="{{$competencie.linkProfile}}" > {{$competencie.username}} </a></div>

    <hr>
{{/foreach}}
</div>
<div class="clear"></div>
<div id="view-contact-end"></div>
