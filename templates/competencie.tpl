<label id="competencie-name-label" for="competencie-name">
    <b>Title:</b>
</label>

{{$competencie.name}}

<br />

<label id="competencie-statement-label" for="competencie-statement">
    <b>Description:</b>
</label>

{{$competencie.statement}}

<div class="profile-edit-side-div" style="display: {{$show}};">
    <a class="icon edit" title="{{$edit}}" href="{{$competencie.edit}}"></a>
    <button class="icon delete" href="" title="{{$del}}" onclick="document.getElementById('form1').submit();"></button>
    <form id="form1" name="form1" action="{{$competencie.del}}" method="post">
    </form>
</div>
<br />
<hr />