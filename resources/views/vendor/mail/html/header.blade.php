@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://siveno.shop/logo.png" class="logo" alt="Siveno Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
