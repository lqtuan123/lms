<div class="modal fade" id="bookTypeModal" tabindex="-1" aria-labelledby="bookTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <form method="GET" action="{{ route('front.book.filter') }}">
        <div class="modal-content shadow rounded-3">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="bookTypeModalLabel">🎯 Chọn danh mục sách</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
            <div class="list-group">
              @foreach ($booktypes as $booktype)
                <label for="type-{{ $booktype->id }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                  <div>
                    <input class="form-check-input me-2" type="checkbox" name="book_types[]" value="{{ $booktype->id }}" id="type-{{ $booktype->id }}">
                    {{ $booktype->title }}
                  </div>
                  <span class="badge bg-secondary rounded-pill">{{ $booktype->active_books_count }}</span>
                </label>
              @endforeach
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-1"></i> Hủy
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i> Lọc sách
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  