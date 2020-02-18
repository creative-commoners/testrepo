<% if $RepliesEnabled %>
    <div class="comment-replies-container">

        <div class="comment-reply-form-holder">
            $ReplyForm
        </div>

        <div class="comment-replies-holder">
            <% if $Replies %>
                <ul class="comments-list level-{$Depth} list-unstyled">
                    <% loop $Replies %>
                        <li class="comment $EvenOdd<% if FirstLast %> $FirstLast <% end_if %> $SpamClass">
                            <% include CommentsInterface_singlecomment %>
                        </li>
                    <% end_loop %>
                </ul>
                <% with $Replies %>
                    <% include ReplyPagination %>
                <% end_with %>
            <% end_if %>
        </div>
    </div>
<% end_if %>
