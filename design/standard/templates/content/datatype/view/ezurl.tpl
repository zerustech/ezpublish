{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{section show=$attribute.data_text}
<a href="{$attribute.content|wash( xhtml )}">{$attribute.data_text|wash( xhtml )}</a>
{section-else}
<a href="{$attribute.content|wash( xhtml )}">{$attribute.content|wash( xhtml )}</a>
{/section}