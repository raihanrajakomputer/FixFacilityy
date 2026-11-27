/**
 * FixFacility - Frontend Interactions & UI UX
 */

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // 1. MODAL KONFIRMASI HAPUS
    // ----------------------------------------------------
    const deleteModal = document.getElementById('modal-konfirmasi-hapus');
    const deleteModalTitle = document.getElementById('modal-hapus-title');
    const deleteModalDesc = document.getElementById('modal-hapus-desc');
    const deleteModalForm = document.getElementById('modal-hapus-form');

    window.bukaModalHapus = function (kode, judul, url) {
        if (!deleteModal) return;
        if (deleteModalTitle) deleteModalTitle.textContent = 'Hapus laporan ini?';
        if (deleteModalDesc) {
            deleteModalDesc.textContent = `Laporan ${kode} — ${judul} akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.`;
        }
        if (deleteModalForm && url) {
            deleteModalForm.action = url;
        }
        deleteModal.classList.add('on');
    };

    window.tutupModalHapus = function () {
        if (deleteModal) {
            deleteModal.classList.remove('on');
        }
    };

    if (deleteModal) {
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                tutupModalHapus();
            }
        });
    }

    // ----------------------------------------------------
    // 2. MODAL KONFIRMASI LOGOUT
    // ----------------------------------------------------
    const logoutModal = document.getElementById('modal-konfirmasi-logout');

    window.bukaModalLogout = function () {
        if (logoutModal) {
            logoutModal.classList.add('on');
        }
    };

    window.tutupModalLogout = function () {
        if (logoutModal) {
            logoutModal.classList.remove('on');
        }
    };

    if (logoutModal) {
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
                tutupModalLogout();
            }
        });
    }

    // ----------------------------------------------------
    // 3. ADMIN STATUS CHIPS & SELECT SYNC
    // ----------------------------------------------------
    const statusSelect = document.getElementById('status-select')
        || document.querySelector('select[name="status_id"], select[name="status"]');
    const statusChips = document.querySelectorAll('.chips .chip-status');

    function resolveChipOptionValue(selectEl, chip) {
        const rawVal = chip.getAttribute('data-status-id') || chip.getAttribute('data-status') || '';
        const options = Array.from(selectEl.options);
        const byValue = options.find(opt => opt.value === rawVal);
        if (byValue) return byValue.value;

        const targetText = (chip.getAttribute('data-status') || chip.textContent || '').trim().toLowerCase();
        const byText = options.find(opt => opt.textContent.trim().toLowerCase() === targetText);
        return byText ? byText.value : rawVal;
    }

    function syncChipsFromSelect() {
        if (!statusSelect || !statusChips.length) return;
        const selectedOption = statusSelect.options[statusSelect.selectedIndex];
        const selectedVal = statusSelect.value;
        const selectedText = selectedOption ? selectedOption.textContent.trim().toLowerCase() : '';

        statusChips.forEach(chip => {
            const chipVal = chip.getAttribute('data-status-id') || chip.getAttribute('data-status') || '';
            const chipText = (chip.getAttribute('data-status') || chip.textContent || '').trim().toLowerCase();
            const isMatch = chipVal === selectedVal || (selectedText && chipText === selectedText);
            chip.classList.toggle('active', isMatch);
        });
    }

    if (statusSelect && statusChips.length) {
        statusChips.forEach(chip => {
            chip.addEventListener('click', () => {
                const targetValue = resolveChipOptionValue(statusSelect, chip);
                statusSelect.value = targetValue;
                statusChips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                statusSelect.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        statusSelect.addEventListener('change', syncChipsFromSelect);
    }

    // ----------------------------------------------------
    // 4. FORM VALIDATION & FILE UPLOAD DENGAN PRATINJAU (BUAT LAPORAN)
    // ----------------------------------------------------
    const formBuatLaporan = document.getElementById('form-buat-laporan');
    const fileInput = document.getElementById('input-foto');
    const dropZone = document.getElementById('drop-foto');
    const dropTitle = document.getElementById('drop-title');
    const dropSubtitle = document.getElementById('drop-subtitle');
    const alertError = document.getElementById('alert-error-form');

    const previewWrapper = document.getElementById('foto-preview-wrapper');
    const previewImg = document.getElementById('foto-preview-img');
    const previewName = document.getElementById('foto-preview-name');
    const previewSize = document.getElementById('foto-preview-size');
    const btnGantiFoto = document.getElementById('btn-ganti-foto');
    const btnHapusFoto = document.getElementById('btn-hapus-foto');
    const fotoErrorMsg = document.getElementById('foto-error-msg');
    const photoGroup = document.getElementById('group-foto');

    let currentPreviewUrl = null;

    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        if (bytes >= 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }
        return (bytes / 1024).toFixed(1) + ' KB';
    }

    function bersihkanFoto() {
        if (fileInput) {
            fileInput.value = '';
        }
        if (currentPreviewUrl) {
            URL.revokeObjectURL(currentPreviewUrl);
            currentPreviewUrl = null;
        }
        if (previewImg) previewImg.src = '';
        if (previewWrapper) previewWrapper.style.display = 'none';
        if (dropZone) {
            dropZone.style.display = 'block';
            dropZone.classList.remove('is-invalid');
        }
        if (photoGroup) photoGroup.classList.remove('err');
    }

    function handleFile(file) {
        if (!file) return;

        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        const isValidType = validTypes.includes(file.type.toLowerCase()) || /\.(jpe?g|png|webp)$/i.test(file.name);
        const isValidSize = file.size <= 5 * 1024 * 1024; // 5 MB

        if (!isValidType) {
            bersihkanFoto();
            if (photoGroup) photoGroup.classList.add('err');
            if (dropZone) dropZone.classList.add('is-invalid');
            if (fotoErrorMsg) {
                fotoErrorMsg.textContent = 'Format foto harus berupa JPG, PNG, atau WEBP.';
                fotoErrorMsg.style.display = 'block';
            }
            return;
        }

        if (!isValidSize) {
            bersihkanFoto();
            if (photoGroup) photoGroup.classList.add('err');
            if (dropZone) dropZone.classList.add('is-invalid');
            if (fotoErrorMsg) {
                fotoErrorMsg.textContent = 'Ukuran foto maksimal 5 MB.';
                fotoErrorMsg.style.display = 'block';
            }
            return;
        }

        // Foto valid: Bersihkan error dan tampilkan pratinjau
        if (photoGroup) photoGroup.classList.remove('err');
        if (dropZone) {
            dropZone.classList.remove('is-invalid');
            dropZone.style.display = 'none';
        }
        if (fotoErrorMsg) fotoErrorMsg.style.display = 'none';

        if (currentPreviewUrl) {
            URL.revokeObjectURL(currentPreviewUrl);
        }
        currentPreviewUrl = URL.createObjectURL(file);

        if (previewImg) previewImg.src = currentPreviewUrl;
        if (previewName) previewName.textContent = file.name;
        if (previewSize) previewSize.textContent = formatFileSize(file.size);
        if (previewWrapper) previewWrapper.style.display = 'block';
    }

    if (dropZone && fileInput) {
        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--pr)';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = '';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '';
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                try {
                    fileInput.files = e.dataTransfer.files;
                } catch (err) {
                    // Fallback
                }
                handleFile(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files.length) {
                handleFile(fileInput.files[0]);
            }
        });
    }

    if (btnGantiFoto && fileInput) {
        btnGantiFoto.addEventListener('click', () => {
            fileInput.click();
        });
    }

    if (btnHapusFoto) {
        btnHapusFoto.addEventListener('click', () => {
            bersihkanFoto();
        });
    }

    if (formBuatLaporan) {
        const fieldGroups = [
            ['judul'],
            ['fasilitas_id', 'fasilitas'],
            ['lokasi'],
            ['kategori_id', 'kategori'],
            ['deskripsi']
        ];

        fieldGroups.forEach(names => {
            const selector = names.map(name => `[name="${name}"]`).join(', ');
            const input = formBuatLaporan.querySelector(selector);
            if (input) {
                const clearErr = () => {
                    const group = input.closest('.g');
                    if (group && input.value.trim()) {
                        group.classList.remove('err');
                    }
                };
                input.addEventListener('input', clearErr);
                input.addEventListener('change', clearErr);
            }
        });

        formBuatLaporan.addEventListener('submit', (e) => {
            let hasError = false;

            fieldGroups.forEach(names => {
                const selector = names.map(name => `[name="${name}"]`).join(', ');
                const input = formBuatLaporan.querySelector(selector);
                if (input) {
                    const group = input.closest('.g');
                    if (!input.value.trim()) {
                        if (group) group.classList.add('err');
                        hasError = true;
                    } else {
                        if (group) group.classList.remove('err');
                    }
                }
            });

            const photoOptional = fileInput && fileInput.dataset.optional === 'true';
            const hasPhoto = fileInput && fileInput.files && fileInput.files.length > 0;
            if (fileInput && !photoOptional && !hasPhoto) {
                if (photoGroup) {
                    photoGroup.classList.add('err');
                    if (dropZone) dropZone.classList.add('is-invalid');
                    if (fotoErrorMsg) {
                        fotoErrorMsg.textContent = 'Foto bukti kerusakan wajib diunggah.';
                        fotoErrorMsg.style.display = 'block';
                    }
                }
                hasError = true;
            }

            // Hanya cegah pengiriman bila data tidak valid di sisi klien
            if (hasError) {
                e.preventDefault();
                if (alertError) alertError.classList.add('show');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }

            // Jika valid, biarkan form terkirim ke backend
            if (alertError) alertError.classList.remove('show');
        });
    }

    // ----------------------------------------------------
    // 5. MODAL LIGHTBOX FOTO DETAIL LAPORAN
    // ----------------------------------------------------
    window.bukaLightbox = function (src, caption) {
        let modal = document.getElementById('modal-lightbox');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'modal-lightbox';
            modal.className = 'lightbox-modal';
            modal.innerHTML = `
                <div class="lightbox-content" onclick="event.stopPropagation()">
                    <button type="button" class="lightbox-close" onclick="tutupLightbox()">&times;</button>
                    <img id="lightbox-img" src="" alt="Pratinjau Foto Penuh">
                    <div id="lightbox-caption" class="lightbox-caption"></div>
                </div>
            `;
            modal.addEventListener('click', window.tutupLightbox);
            document.body.appendChild(modal);
        }

        const img = document.getElementById('lightbox-img');
        const cap = document.getElementById('lightbox-caption');
        if (img) img.src = src;
        if (cap) cap.textContent = caption || '';

        modal.classList.add('on');
        document.body.style.overflow = 'hidden';
    };

    window.tutupLightbox = function () {
        const modal = document.getElementById('modal-lightbox');
        if (modal) {
            modal.classList.remove('on');
        }
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.tutupLightbox();
            window.tutupModalHapus();
            window.tutupModalLogout();
        }
    });

    // ----------------------------------------------------
    // 6. LOGIN FORM VALIDATION (CLIENT-SIDE)
    // ----------------------------------------------------
    const loginForm = document.getElementById('form-login');
    if (loginForm) {
        const nimInput = document.getElementById('nim') || loginForm.querySelector('[name="nim_nip"], [name="nim"]');
        const pwInput = document.getElementById('password') || loginForm.querySelector('[name="password"]');
        const g1 = document.getElementById('g-nim') || (nimInput ? nimInput.closest('.g') : null);
        const g2 = document.getElementById('g-password') || (pwInput ? pwInput.closest('.g') : null);
        const e1 = document.getElementById('e1') || (g1 ? g1.querySelector('.em') : null);
        const e2 = document.getElementById('e2') || (g2 ? g2.querySelector('.em') : null);

        if (nimInput && g1) {
            nimInput.addEventListener('input', () => {
                if (nimInput.value.trim()) g1.classList.remove('err');
            });
        }
        if (pwInput && g2) {
            pwInput.addEventListener('input', () => {
                if (pwInput.value) g2.classList.remove('err');
            });
        }

        loginForm.addEventListener('submit', (e) => {
            const nimVal = nimInput ? nimInput.value.trim() : '';
            const pwVal = pwInput ? pwInput.value : '';

            let hasError = false;

            if (!nimVal) {
                if (g1) g1.classList.add('err');
                if (e1) e1.textContent = 'NIM/NIP wajib diisi.';
                hasError = true;
            } else {
                if (g1) g1.classList.remove('err');
            }

            if (!pwVal) {
                if (g2) g2.classList.add('err');
                if (e2) e2.textContent = 'Kata sandi wajib diisi.';
                hasError = true;
            } else {
                if (g2) g2.classList.remove('err');
            }

            // Hanya cegah submit bila ada field yang kosong di sisi klien
            if (hasError) {
                e.preventDefault();
                return false;
            }

            // Jika valid di sisi klien, biarkan form terkirim ke POST /login backend
        });
    }

    // ----------------------------------------------------
    // 7. MODERN ANIMATED CUSTOM DROPDOWN (ALL SELECTS)
    // ----------------------------------------------------
    const enhancedWrappers = [];

    function closeAllDropdowns(exceptWrapper = null) {
        enhancedWrappers.forEach(item => {
            if (item.wrapper !== exceptWrapper) {
                item.close(false);
            }
        });
    }

    function enhanceSelect(nativeSelect) {
        if (!nativeSelect || nativeSelect.dataset.enhanced === 'true') return;
        nativeSelect.dataset.enhanced = 'true';

        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        if (nativeSelect.classList.contains('is-invalid')) {
            wrapper.classList.add('is-invalid');
        }

        nativeSelect.parentNode.insertBefore(wrapper, nativeSelect);
        wrapper.appendChild(nativeSelect);
        nativeSelect.classList.add('custom-select-native');
        nativeSelect.tabIndex = -1;
        nativeSelect.setAttribute('aria-hidden', 'true');

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'custom-select-trigger';
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const valueSpan = document.createElement('span');
        valueSpan.className = 'custom-select-value';

        const chevronSpan = document.createElement('span');
        chevronSpan.className = 'custom-select-chevron';
        chevronSpan.setAttribute('aria-hidden', 'true');
        chevronSpan.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';

        trigger.appendChild(valueSpan);
        trigger.appendChild(chevronSpan);

        const menu = document.createElement('div');
        menu.className = 'custom-select-menu';
        menu.setAttribute('role', 'listbox');

        const optionButtons = [];
        let highlightedIdx = -1;

        function setHighlighted(index) {
            optionButtons.forEach((btn, i) => {
                btn.classList.toggle('is-highlighted', i === index);
            });
            highlightedIdx = index;
            if (index >= 0 && optionButtons[index]) {
                optionButtons[index].scrollIntoView({ block: 'nearest' });
            }
        }

        function syncFromNative() {
            const selectedOption = nativeSelect.options[nativeSelect.selectedIndex] || nativeSelect.options[0];
            if (selectedOption) {
                valueSpan.textContent = selectedOption.textContent;
                valueSpan.classList.toggle('is-placeholder', selectedOption.value === '');
            } else {
                valueSpan.textContent = '';
                valueSpan.classList.add('is-placeholder');
            }

            optionButtons.forEach(btn => {
                const isSelected = btn.dataset.value === nativeSelect.value;
                btn.classList.toggle('is-selected', isSelected);
                btn.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });

            trigger.disabled = nativeSelect.disabled;
            trigger.classList.toggle('is-disabled', nativeSelect.disabled);
        }

        function openMenu() {
            if (nativeSelect.disabled) return;
            closeAllDropdowns(wrapper);

            const rect = trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            wrapper.classList.toggle('open-up', spaceBelow < 240 && spaceAbove > spaceBelow);

            wrapper.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');

            const selectedIdx = optionButtons.findIndex(btn => btn.dataset.value === nativeSelect.value);
            setHighlighted(selectedIdx >= 0 ? selectedIdx : 0);
        }

        function closeMenu(restoreFocus = false) {
            wrapper.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            setHighlighted(-1);
            if (restoreFocus) {
                trigger.focus();
            }
        }

        function selectOption(value) {
            const changed = nativeSelect.value !== value;
            nativeSelect.value = value;
            syncFromNative();
            closeMenu(true);

            if (changed) {
                nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        Array.from(nativeSelect.options).forEach((opt, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'custom-select-option';
            btn.setAttribute('role', 'option');
            btn.dataset.value = opt.value;
            btn.dataset.index = String(idx);

            if (opt.value === '') {
                btn.classList.add('is-placeholder-option');
            }
            if (opt.disabled) {
                btn.classList.add('is-disabled');
                btn.disabled = true;
            }

            const labelSpan = document.createElement('span');
            labelSpan.textContent = opt.textContent;

            const checkSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            checkSvg.setAttribute('class', 'custom-select-check');
            checkSvg.setAttribute('viewBox', '0 0 24 24');
            checkSvg.setAttribute('fill', 'none');
            checkSvg.setAttribute('stroke', 'currentColor');
            checkSvg.setAttribute('stroke-width', '2.5');
            checkSvg.setAttribute('stroke-linecap', 'round');
            checkSvg.setAttribute('stroke-linejoin', 'round');
            checkSvg.setAttribute('aria-hidden', 'true');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', 'M20 6 9 17l-5-5');
            checkSvg.appendChild(path);

            btn.appendChild(labelSpan);
            btn.appendChild(checkSvg);

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (!opt.disabled) {
                    selectOption(opt.value);
                }
            });

            btn.addEventListener('mouseenter', () => {
                if (!opt.disabled) {
                    setHighlighted(idx);
                }
            });

            menu.appendChild(btn);
            optionButtons.push(btn);
        });

        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);

        syncFromNative();

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (wrapper.classList.contains('is-open')) {
                closeMenu(false);
            } else {
                openMenu();
            }
        });

        trigger.addEventListener('keydown', (e) => {
            const isOpen = wrapper.classList.contains('is-open');

            if (!isOpen && ['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(e.key)) {
                e.preventDefault();
                openMenu();
                return;
            }

            if (!isOpen) return;

            const enabledIndices = optionButtons
                .map((b, i) => (b.disabled ? -1 : i))
                .filter(i => i !== -1);
            if (!enabledIndices.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const pos = enabledIndices.indexOf(highlightedIdx);
                const next = pos === -1 ? enabledIndices[0] : enabledIndices[(pos + 1) % enabledIndices.length];
                setHighlighted(next);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const pos = enabledIndices.indexOf(highlightedIdx);
                const prev = pos === -1
                    ? enabledIndices[enabledIndices.length - 1]
                    : enabledIndices[(pos - 1 + enabledIndices.length) % enabledIndices.length];
                setHighlighted(prev);
            } else if (e.key === 'Home') {
                e.preventDefault();
                setHighlighted(enabledIndices[0]);
            } else if (e.key === 'End') {
                e.preventDefault();
                setHighlighted(enabledIndices[enabledIndices.length - 1]);
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (highlightedIdx >= 0 && optionButtons[highlightedIdx] && !optionButtons[highlightedIdx].disabled) {
                    selectOption(optionButtons[highlightedIdx].dataset.value);
                } else {
                    closeMenu(true);
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeMenu(true);
            } else if (e.key === 'Tab') {
                closeMenu(false);
            }
        });

        nativeSelect.addEventListener('change', () => {
            syncFromNative();
            const group = nativeSelect.closest('.g');
            if (group && nativeSelect.value.trim()) {
                group.classList.remove('err');
            }
        });

        nativeSelect.addEventListener('focus', () => {
            trigger.focus();
        });

        if (nativeSelect.id) {
            const label = document.querySelector(`label[for="${CSS.escape(nativeSelect.id)}"]`);
            if (label) {
                label.addEventListener('click', (e) => {
                    e.preventDefault();
                    trigger.focus();
                });
            }
        }

        enhancedWrappers.push({ wrapper, close: closeMenu });
    }

    document.querySelectorAll('select').forEach(enhanceSelect);

    document.addEventListener('click', (e) => {
        enhancedWrappers.forEach(item => {
            if (!item.wrapper.contains(e.target)) {
                item.close(false);
            }
        });
    });
});
