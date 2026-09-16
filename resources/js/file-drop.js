document.addEventListener('alpine:init', () => {
    Alpine.data('fileDrop', () => ({
        dragging: false,
        fileName: '',
        previewUrl: null,
        isPdf: false,

        handleDrop(e) {
            this.dragging = false;
            const files = e.dataTransfer.files;
            if (files.length) {
                this.$refs.input.files = files;
                this.setPreview(files[0]);
            }
        },

        handleChange(e) {
            const files = e.target.files;
            if (files.length) this.setPreview(files[0]);
        },

        setPreview(file) {
            this.fileName = file.name;
            this.isPdf = file.type === 'application/pdf';

            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
            }

            this.previewUrl = this.isPdf ? null : URL.createObjectURL(file);
        },
    }));

    Alpine.data('photoPicker', (max = 5) => ({
        dragging: false,
        photos: [],
        dragIndex: null,

        addFiles(fileList) {
            const incoming = Array.from(fileList).filter((f) => f.type.startsWith('image/'));
            const room = max - this.photos.length;
            incoming.slice(0, room).forEach((file) => {
                this.photos.push({ file, url: URL.createObjectURL(file) });
            });
            this.sync();
        },

        handleDrop(e) {
            this.dragging = false;
            this.addFiles(e.dataTransfer.files);
        },

        handleChange(e) {
            this.addFiles(e.target.files);
        },

        remove(index) {
            URL.revokeObjectURL(this.photos[index].url);
            this.photos.splice(index, 1);
            this.sync();
        },

        // Reordering (drag a thumbnail onto another to swap its position — the
        // first photo becomes the cover, same as the "Current photos" grid).
        reorderStart(index) {
            this.dragIndex = index;
        },

        reorderDrop(index) {
            if (this.dragIndex === null || this.dragIndex === index) return;
            const [moved] = this.photos.splice(this.dragIndex, 1);
            this.photos.splice(index, 0, moved);
            this.dragIndex = null;
            this.sync();
        },

        sync() {
            const dt = new DataTransfer();
            this.photos.forEach((p) => dt.items.add(p.file));
            this.$refs.input.files = dt.files;
        },
    }));
});
