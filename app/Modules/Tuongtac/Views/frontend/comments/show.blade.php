 {{-- phần bình luận  --}}
 @php
     use Carbon\Carbon;
 @endphp
 <style>
     .comments-section {
         margin: 20px 0;
         padding: 15px;
         border: 1px solid #ddd;
         border-radius: 8px;
         background-color: #f9f9f9;
     }

     .comments-section h3 {
         font-size: 20px;
         margin-bottom: 15px;
         color: #333;
         font-weight: bold;
         text-transform: uppercase;
     }

     .comment-form {
         margin-bottom: 20px;
         display: flex;
         flex-direction: column;
     }

     .comment-form textarea {
         width: 100%;
         height: 80px;
         resize: none;
         padding: 10px;
         font-size: 14px;
         border: 1px solid #ddd;
         border-radius: 6px;
     }

     .comment-form button {
         margin-top: 10px;
         padding: 8px 15px;
         font-size: 14px;
         background-color: #007bff;
         color: white;
         border: none;
         border-radius: 6px;
         cursor: pointer;
         transition: background-color 0.3s;
     }

     .comment-form button:hover {
         background-color: #0056b3;
     }

     .comments-list {
         margin-top: 15px;
     }

     .comment {
         margin-bottom: 15px;
         padding: 10px;
         border: 1px solid #ddd;
         border-radius: 6px;
         background-color: white;
     }

     .comment-author {
         font-weight: bold;
         margin-bottom: 5px;
         color: #007bff;
     }

     .comment-text {
         margin-bottom: 10px;
         color: #333;
         font-size: 14px;
     }

     .comment-actions {
         font-size: 12px;
         color: #555;
         display: flex;
         align-items: center;
         gap: 10px;
     }

     .comment-actions span {
         cursor: pointer;
         color: #007bff;
         transition: color 0.3s;
     }

     .comment-actions span:hover {
         color: #0056b3;
     }

     .reply-form {
         margin-top: 10px;
         display: none;
         flex-direction: column;
     }

     .reply-form textarea {
         width: 100%;
         height: 60px;
         resize: none;
         padding: 8px;
         font-size: 14px;
         border: 1px solid #ddd;
         border-radius: 6px;
     }

     .reply-form button {
         margin-top: 5px;
         padding: 5px 10px;
         font-size: 12px;
         background-color: #28a745;
         color: white;
         border: none;
         border-radius: 4px;
         cursor: pointer;
         transition: background-color 0.3s;
     }

     .reply-form button:hover {
         background-color: #218838;
     }

     .replies {
         margin-left: 20px;
         padding: 10px;
         border-left: 2px solid #ddd;
     }

     .reply {
         margin-bottom: 10px;
         background-color: #f1f1f1;
         padding: 8px;
         border-radius: 6px;
     }

     .reply-author {
         font-weight: bold;
         margin-bottom: 5px;
         color: #007bff;
     }

     .reply-text {
         margin-bottom: 10px;
         color: #333;
         font-size: 14px;
     }

     .reply-actions {
         font-size: 12px;
         color: #555;
         display: flex;
         align-items: center;
         gap: 10px;
     }

     .reply-actions span {
         cursor: pointer;
         color: #007bff;
         transition: color 0.3s;
     }

     .reply-actions span:hover {
         color: #0056b3;
     }

     .comment_save_btn,
     .comment_cancel_btn {
         margin-left: 5px;
         padding: 3px 8px;
         font-size: 12px;
         border-radius: 4px;
         cursor: pointer;
         transition: background-color 0.3s;
     }

     .comment_save_btn {
         background-color: #28a745;
         color: white;
         border: none;
     }

     .comment_save_btn:hover {
         background-color: #218838;
     }

     .comment_cancel_btn {
         background-color: #dc3545;
         color: white;
         border: none;
     }

     .comment_cancel_btn:hover {
         background-color: #c82333;
     }
 </style>
 <div class="comments-section">
     <h3>Bình luận</h3>

     <!-- Form nhập bình luận mới -->
     <div class="comment-form">
         <textarea id="te-{{ $item_id }}" placeholder="Viết bình luận của bạn..."></textarea>
         <button onclick="submitComment({{ $item_id }})">Gửi</button>
     </div>

     <!-- Danh sách bình luận -->
     <div class="comments-list" id='comments-list-{{ $item_id }}'>
         <!-- Một bình luận chính -->
         @foreach ($comments as $comment)
             @php
                 $createdAt = Carbon::parse($comment->created_at); // Thay đổi $comment thành đối tượng bạn đang sử dụng
                 $diffInMinutes = $createdAt->diffInMinutes();
                 $diffInHours = $createdAt->diffInHours();
                 $diffInDays = $createdAt->diffInDays();
                 $thoigian = '';
                 if ($diffInMinutes < 60) {
                     $thoigian = $diffInMinutes . ' phút trước';
                 } elseif ($diffInHours < 24) {
                     $thoigian = floor($diffInHours) . ' tiếng trước';
                 } else {
                     $thoigian = floor($diffInDays) . ' ngày trước';
                 }
             @endphp
             <div id='acomment-{{ $item_id }}-{{ $comment->id }}' class="comment">
                 <div class="comment-author">{{ $comment->full_name }}</div>
                 <div id="comment-text-{{ $comment->id }}"class="comment-text">{{ $comment->content }}</div>
                 <div class="comment-actions">
                     <span onclick="replyComment({{ $comment->id }})">Phản hồi</span>
                     @if (isset($curuser) && $curuser->id == $comment->user_id)
                         | <span onclick="editComment({{ $item_id }},{{ $comment->id }})">Chỉnh sửa</span>
                         | <span onclick="deleteComment({{ $item_id }},{{ $comment->id }})"
                             class="text-danger">Xóa</span>
                     @endif
                     |
                     <span>{{ $thoigian }}</span>
                 </div>
                 <!-- Form nhập phản hồi, mặc định ẩn -->
                 <div class="reply-form" id="reply-form-{{ $comment->id }}" style="display: none;">
                     <textarea id="te-{{ $item_id }}-{{ $comment->id }}" placeholder="Phản hồi lại..."></textarea>
                     <button onclick="submitReply({{ $item_id }},{{ $comment->id }})">Gửi</button>
                 </div>
                 <div id= "comment-{{ $item_id }}-{{ $comment->id }}">

                     @foreach ($comment->subcomments as $subcomment)
                         <!-- Danh sách các phản hồi cho bình luận chính -->
                         @php
                             $createdAt = Carbon::parse($subcomment->created_at); // Thay đổi $comment thành đối tượng bạn đang sử dụng
                             $diffInMinutes = $createdAt->diffInMinutes();
                             $diffInHours = $createdAt->diffInHours();
                             $diffInDays = $createdAt->diffInDays();
                             $thoigiansub = '';
                             if ($diffInMinutes < 60) {
                                 $thoigian = $diffInMinutes . ' phút trước';
                             } elseif ($diffInHours < 24) {
                                 $thoigian = floor($diffInHours) . ' tiếng trước';
                             } else {
                                 $thoigian = floor($diffInDays) . ' ngày trước';
                             }
                         @endphp
                         <div id='acomment-{{ $item_id }}-{{ $subcomment->id }}' class="replies">
                             <div class="reply">
                                 <div class="reply-author">{{ $subcomment->full_name }}</div>
                                 <div class="reply-text" id ="reply-text-{{ $subcomment->id }}">
                                     {{ $subcomment->content }}</div>
                                 <div class="reply-actions">
                                     <span onclick="replyComment({{ $subcomment->id }})">Phản hồi</span>
                                     @if (isset($curuser) && $curuser->id == $subcomment->user_id)
                                         | <span onclick="editReply({{ $item_id }},{{ $subcomment->id }})">Chỉnh
                                             sửa</span>
                                         | <span onclick="deleteComment({{ $item_id }},{{ $subcomment->id }})"
                                             class="text-danger">Xóa</span>
                                     @endif
                                     |
                                     <span>{{ $thoigiansub }}</span>
                                 </div>
                             </div>
                             <div class="reply-form" id="reply-form-{{ $subcomment->id }}" style="display: none;">
                                 <textarea id="tes-{{ $item_id }}-{{ $comment->id }}-{{ $subcomment->id }}" placeholder="Phản hồi lại..."></textarea>
                                 <button
                                     onclick="submitSubReply({{ $item_id }},{{ $comment->id }},{{ $subcomment->id }})">Gửi</button>
                             </div>
                         </div>
                     @endforeach
                 </div>
             </div>
         @endforeach
     </div>
 </div>

 <script>
     if (typeof myVariable !== 'undefined') {
         // Biến `myVariable` đã được định nghĩa
     } else {
         var item_code = '{{ $item_code }}';
     }
     var item_code = '{{ $item_code }}';
     if (typeof replyComment !== 'function') {

         function replyComment(commentId) {

             // Tìm form phản hồi dựa trên commentId và thay đổi trạng thái hiển thị
             const replyForm = document.getElementById(`reply-form-${commentId}`);
             if (replyForm.style.display === "none") {
                 replyForm.style.display = "block";
             } else {
                 replyForm.style.display = "none";
             }
         }
     }
     if (typeof submitComment !== 'function') {
         function submitComment(item_id) {
             // Xử lý gửi bình luận mới
             const content = document.getElementById(`te-${item_id}`).value;
             if (content.trim() === '') {
                 alert('Vui lòng nhập nội dung bình luận.');
                 return;
             }
             const dataToSend = {
                 _token: "{{ csrf_token() }}",
                 parent_id: 0,
                 content: content.trim(),
                 item_id: item_id,
                 item_code: item_code
             };
             $.ajax({
                 url: "{{ route('front.tcomments.savecomment') }}", // Replace with your actual server endpoint URL
                 method: "POST",
                 contentType: "application/json",
                 data: JSON.stringify(dataToSend),
                 success: function(response) {
                     if (response.status) {
                         // document.getElementById('te-{{ $item_id }}').value = '';
                         document.getElementById(`te-${item_id}`).value = "";
                         addCommentToDOM(item_id, response.msg);
                         // location.reload(); // Tải lại trang để thấy bình luận mới
                     } else {
                         alert(response.msg);
                     }
                 },
                 error: function(error) {
                     console.error(error);
                     alert('Có lỗi xảy ra khi gửi bình luận.');
                 }
             });
         }
     }


     if (typeof addCommentToDOM !== 'function') {
         function addCommentToDOM(item_id, comment) {
             const commentSection = document.getElementById(`comments-list-${item_id}`);

             // Tạo phần tử HTML mới cho bình luận
             const newComment = document.createElement('div');
             newComment.className = 'comment';
             newComment.innerHTML = `
            <div class="comment-author">${comment.full_name}</div>
            <div class="comment-text" id="comment-text-${comment.id}">${comment.content}</div>
            <div class="comment-actions">
                <span onclick="replyComment(${comment.id})">Phản hồi</span> | 
                <span>mới tạo</span>
                | <span onclick="editComment(${item_id},${comment.id})">Chỉnh sửa</span>
                | <span onclick="deleteComment(${item_id},${comment.id})" class="text-danger">Xóa</span>
            </div>
            <div class="reply-form" id="reply-form-${comment.id}" style="display: none;">
                <textarea id="te-${item_id}-${comment.id}" placeholder="Phản hồi lại..."></textarea>
                <button onclick="submitReply(${item_id},${comment.id})">Gửi</button>
            </div>
            <div id="comment-${item_id}-${comment.id}"  >
            </div>
        `;

             // Thêm bình luận mới vào DOM
             commentSection.prepend(newComment);
         }
     }

     if (typeof addReplyToDOM !== 'function') {
         function addReplyToDOM(item_id, subcomment, comment_id) {
             const commentSection = document.getElementById(`comment-${item_id}-${comment_id}`);

             // Tạo phần tử HTML mới cho bình luận
             const newComment = document.createElement('div');
             newComment.className = 'comment';
             newComment.innerHTML = `
             <div class="replies">
                <div class="reply">
                    <div class="reply-author">${subcomment.full_name}</div>
                    <div class="reply-text" id ="reply-text-${subcomment.id}">${subcomment.content}</div>
                    <div class="reply-actions">
                        <span onclick="replyComment(${subcomment.id})">Phản hồi</span> 
                        
                        |   <span onclick="editReply(${item_id}, ${subcomment.id})">Chỉnh sửa</span>
                        |   <span onclick="deleteComment(${item_id},${subcomment.id})" class="text-danger">Xóa</span>
                        
                        | 
                        <span>mới tạo</span>
                    </div>
                </div>
                <div class="reply-form" id="reply-form-${subcomment.id}" style="display: none;">
                    <textarea id="tes-${item_id}-${comment_id}-${subcomment.id}" placeholder="Phản hồi lại..."></textarea>
                    <button onclick="submitSubReply(${item_id},${comment_id},${subcomment.id})">Gửi</button>
                </div>
            </div>
        `;

             // Thêm bình luận mới vào DOM
             commentSection.prepend(newComment);
         }
     }
     if (typeof submitReply !== 'function') {
         function submitReply(item_id, commentId) {
             const content = document.getElementById(`te-${item_id}-${commentId}`).value;
             if (content.trim() === '') {
                 alert('Vui lòng nhập nội dung bình luận.');
                 return;
             }
             const dataToSend = {
                 _token: "{{ csrf_token() }}",
                 parent_id: commentId,
                 content: content.trim(),
                 item_id: item_id,
                 item_code: item_code
             };
             $.ajax({
                 url: "{{ route('front.tcomments.savecomment') }}",
                 method: "POST",
                 contentType: "application/json",
                 data: JSON.stringify(dataToSend),
                 success: function(response) {
                     if (response.status) {
                         document.getElementById(`te-${item_id}-${commentId}`).value = '';
                         document.getElementById(`reply-form-${commentId}`).style.display =
                             "none"; // Ẩn ô phản hồi
                         addReplyToDOM(item_id, response.msg, commentId);
                     } else {
                         alert(response.msg);
                     }
                 },
                 error: function(error) {
                     console.error(error);
                     alert('Có lỗi xảy ra khi gửi bình luận.');
                 }
             });
         }

     }
     if (typeof submitSubReply !== 'function') {
         function submitSubReply(item_id, commentId, subcommentId) {
             const content = document.getElementById(`tes-${item_id}-${commentId}-${subcommentId}`).value;
             if (content.trim() === '') {
                 alert('Vui lòng nhập nội dung bình luận.');
                 return;
             }
             const dataToSend = {
                 _token: "{{ csrf_token() }}",
                 parent_id: commentId,
                 content: content.trim(),
                 item_id: item_id,
                 item_code: item_code
             };
             $.ajax({
                 url: "{{ route('front.tcomments.savecomment') }}",
                 method: "POST",
                 contentType: "application/json",
                 data: JSON.stringify(dataToSend),
                 success: function(response) {
                     if (response.status) {
                         document.getElementById(`tes-${item_id}-${commentId}-${subcommentId}`).value = '';
                         document.getElementById(`reply-form-${subcommentId}`).style.display =
                             "none"; // Ẩn ô phản hồi con
                         addReplyToDOM(item_id, response.msg, commentId);
                     } else {
                         alert(response.msg);
                     }
                 },
                 error: function(error) {
                     console.error(error);
                     alert('Có lỗi xảy ra khi gửi bình luận.');
                 }
             });
         }

     }
     if (typeof editComment !== 'function') {
         function editComment(item_id, commentId) {
             const commentTextEl = document.getElementById(`comment-text-${commentId}`);
             const originalText = commentTextEl.textContent;

             // Tạo một ô nhập liệu để chỉnh sửa
             commentTextEl.innerHTML = `
            <input type="text" id="edit-input-${commentId}" value="${originalText}" />
            <button class="comment_save_btn" onclick="saveComment(${item_id},${commentId})">Lưu</button>
            <button class="comment_cancel_btn" onclick="cancelEdit(${commentId}, '${originalText}')">Hủy</button>
        `;
         }
     }
     if (typeof editReply !== 'function') {
         function editReply(item_id, commentId) {
             const commentTextEl = document.getElementById(`reply-text-${commentId}`);
             const originalText = commentTextEl.textContent;

             // Tạo một ô nhập liệu để chỉnh sửa
             commentTextEl.innerHTML = `
            <input type="text" id="edit-input-${commentId}" value="${originalText}" />
            <button class="comment_save_btn" onclick="saveReply(${item_id},${commentId})">Lưu</button>
            <button class="comment_cancel_btn" onclick="cancelReply(${commentId}, '${originalText}')">Hủy</button>
        `;
         }
     }
     if (typeof saveComment !== 'function') {
         function saveComment(item_id, commentId) {
             const newText = document.getElementById(`edit-input-${commentId}`).value;
             const dataToSend = {
                 _token: "{{ csrf_token() }}",
                 id: commentId,
                 content: newText.trim(),
                 item_id: item_id,
                 item_code: item_code
             };
             $.ajax({
                 url: "{{ route('front.tcomments.updatecomment') }}", // Replace with your actual server endpoint URL
                 method: "POST",
                 contentType: "application/json",
                 data: JSON.stringify(dataToSend),
                 success: function(response) {
                     if (response.status) {
                         document.getElementById(`comment-text-${commentId}`).textContent = newText;
                     } else {
                         alert(response.msg);
                     }
                 },
                 error: function(error) {
                     console.error(error);
                     alert('Có lỗi xảy ra khi cập nhật bình luận.');
                 }
             });

             // Gửi yêu cầu AJAX để cập nhật bình luận

         }
     }
     if (typeof saveReply !== 'function') {
         function saveReply(item_id, commentId) {
             const newText = document.getElementById(`edit-input-${commentId}`).value;
             const dataToSend = {
                 _token: "{{ csrf_token() }}",
                 id: commentId,
                 content: newText.trim(),
                 item_id: item_id,
                 item_code: item_code
             };
             $.ajax({
                 url: "{{ route('front.tcomments.updatecomment') }}", // Replace with your actual server endpoint URL
                 method: "POST",
                 contentType: "application/json",
                 data: JSON.stringify(dataToSend),
                 success: function(response) {
                     if (response.status) {
                         document.getElementById(`reply-text-${commentId}`).textContent = newText;
                     } else {
                         alert(response.msg);
                     }
                 },
                 error: function(error) {
                     console.error(error);
                     alert('Có lỗi xảy ra khi cập nhật bình luận.');
                 }
             });

             // Gửi yêu cầu AJAX để cập nhật bình luận

         }
     }
     if (typeof cancelEdit !== 'function') {
         function cancelEdit(commentId, originalText) {
             document.getElementById(`comment-text-${commentId}`).textContent = originalText;
         }
     }
     if (typeof cancelReply !== 'function') {
         function cancelReply(commentId, originalText) {
             document.getElementById(`reply-text-${commentId}`).textContent = originalText;
         }
     }
     if (typeof deleteComment !== 'function') {
         function deleteComment(item_id, commentId) {
             if (confirm("Bạn có chắc chắn muốn xóa bình luận này không?")) {

                 const dataToSend = {
                     _token: "{{ csrf_token() }}",
                     id: commentId,
                     item_id: item_id,
                     item_code: item_code
                 };
                 $.ajax({
                     url: "{{ route('front.tcomments.deletecomment') }}", // Replace with your actual server endpoint URL
                     method: "POST",
                     contentType: "application/json",
                     data: JSON.stringify(dataToSend),
                     success: function(response) {
                         if (response.status) {
                             document.getElementById(`acomment-${item_id}-${commentId}`).remove();
                         } else {
                             alert(response.msg);
                         }
                     },
                     error: function(error) {
                         console.error(error);
                         alert('Có lỗi xảy ra khi cập nhật bình luận.');
                     }
                 });
             }
         }
     }
 </script>
