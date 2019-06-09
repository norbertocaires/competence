{{$tab_str}}

<div id="viewcontact_wrapper-{{$id}}">
{{foreach $competencies as $competencie}}
    <div><b>Title:</b> {{$competencie.name}}</div>
    <div><b>Description:</b> {{$competencie.statement}}</div>
    <div><b>User:</b> <a title="{{$edit}}" href="{{$competencie.linkProfile}}" > {{$competencie.username}} </a></div>

    <hr>
{{/foreach}}
</div>
<div class="clear"></div>
<div id="view-contact-end"></div>
