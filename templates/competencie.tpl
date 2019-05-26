<label id="competencie-name-label" for="competencie-name" > Título: </label>
<div>{{$competencie.name}}</div>
<label id="competencie-statement-label" for="competencie-statement"> Descrição: </label>
<div>{{$competencie.statement}}</div>
<div class="profile-edit-side-div" style="display: {{$show}};" >
    <a class="icon edit" title="{{$edit}}" href="{{$competencie.edit}}" >
    </a>
    <form id="form1" name="form1" action="{{$competencie.del}}" method="post" >
     <!--   <input class="icon delete" id="event-submit" type="submit" name="submit" value="$del" /> -->
        <button class="icon delete" href="" onclick="document.getElementById('form1').submit();" >
    </form>
    </a>
</div>
<br/>
<hr/>
