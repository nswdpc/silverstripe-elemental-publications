<div class="element-content<% if $ExtraClass %> $ExtraClass<% end_if %>">
	<% if $ShowTitle %>
        <h2 class="element-title<% if $SetAlert %> alert-heading<% end_if %>">$Title</h2>
    <% end_if %>
    $HTML
    <% if $Items %>
        <ul class="items">
            <% loop $Items %>
                <% if $Extension %>
                    <li><a href="$Link">$Title</a> ($Extension.UpperCase, $Size)</li>
                <% else %>
                    <li>{$Me}</li>
                <% end_if %>
            <% end_loop %>
        </ul>
    <% end_if %>
</div>
