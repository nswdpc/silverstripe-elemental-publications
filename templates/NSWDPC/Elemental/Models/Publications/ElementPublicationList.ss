<div class="element-content<% if $ExtraClass %> {$ExtraClass}<% end_if %>">
	<% if $ShowTitle && $Title %>
        <h2 class="element-title">{$Title}</h2>
    <% end_if %>
    <% if $HTML %>
    {$HTML}
    <% end_if %>
    <% if $Items %>
        <ul class="items">
            <% loop $Items %>
                <% if $Extension %>
                    <li><a href="{$Link}">{$Title}</a> ({$Extension.UpperCase}, {$Size})</li>
                <% else %>
                    <li>{$Me}</li>
                <% end_if %>
            <% end_loop %>
        </ul>
    <% end_if %>
</div>
