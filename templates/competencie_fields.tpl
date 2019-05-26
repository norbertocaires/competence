<h3>{{$title}}</h3>

<br>

<form id="competencie-form" name="form1" action="{{$action}}/{{$nick}}" method="post" >
<div>
<label id="competencie-name-label" for="competencie-name" > Título </label>
<br>
<input type="text" size="32" name="competencie_name" id="competencie-name" value="{{$competencie.name}}" /><div class="required">*</div>
</div>

<br>
<div>
<label id="competencie-statement-label" for="competencie-statement"> Descrição </label>
<br>
<textarea rows="10" cols="72" id="competencie_statement" name="competencie_statement" >{{$competencie.statement}}</textarea>
<div class="required">*</div>
</div>

<div class="competencie-submit-wrapper" >
    <input id="event-submit" type="submit" name="submit" value="Salvar" />
</div>
<div class="competencie-submit-end"></div>
</form>
