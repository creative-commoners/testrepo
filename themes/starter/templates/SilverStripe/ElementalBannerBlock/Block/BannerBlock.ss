<%--
    PLEASE NOTE: This template is no longer in use. It will be used if you have silverstripe/elemental-blocks
    installed (deprecated). If you are using silverstripe/elemental-bannerblock, please use the template
    at templates/SilverStripe/ElementalBannerBlock/Block/BannerBlock.ss instead.

    This template will be removed in the next major version.
--%>
<%-- Display the image (File) --%>
<% if $File %>
    <span class="banner-element__image">
        $File
    </span>
<% end_if %>

<div class="banner-element__content">
    <% if $Title && $ShowTitle %>
        <h2 class="banner-element__title">$Title</h2>
    <% end_if %>

    $Content.RichLinks

    <%-- Add a CallToActionLink if available --%>
    <% if $CallToActionLink.Page.Link %>
        <div class="banner-element__call-to-action-container">
        <% with $CallToActionLink %>
            <a href="{$Page.Link}" class="banner-element__call-to-action"
                <% if $TargetBlank %>target="_blank"<% end_if %>
                <% if $Description %>title="{$Description.ATT}"<% end_if %>>
                {$Text.XML}
            </a>
        <% end_with %>
        </div>
    <% end_if %>
</div>
