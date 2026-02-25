const { createApp } = Vue;

// Datos iniciales
const initialSongs = ["Uno, dos, tres", "ABC", "Cabeza, hombros, rodillas y pies", "A la Vibora de la Mar", "El Trenecito", "Si estas Feliz y lo sabes", "La Cancion del Enojo", "Saco una Manita"];
const initialStories = ["Los Tres Cerditos", "La Gallinita Roja", "La Liebre y la Tortuga", "El Leon y el Raton"];
const initialSounds = ["Animales", "Transporte", "Objetos Cotidianos", "Naturaleza"];

createApp({
    delimiters: ['[[', ']]'],
    data() {
        return {
            currentMode: 'tutor',
            currentSection: 'navi',
            activeCategory: null,
            showModal: false,
            showCategoryModal: false,
            showLibraryModal: false,
            showStatsModal: false,
            showEditProfileModal: false,
            currentCategory: null,
            currentLibraryCategory: null,
            currentStatsView: null,
            statsPeriod: 'Semanal',
            selectedGame: '',
            gameDescription: '',
            showToast: false,
            toastMessage: '',
            isTalking: false,
            naviMessage: 'Hola, en que puedo ayudarte hoy?',
            themePreference: 'light',
            isDarkTheme: false,
            systemThemeMediaQuery: null,
            audioSettings: {
                master: 75
            },

            juegos: {
                habilidadComunicativa: Array(8).fill(null).map(() => ({ content: '', editing: false, originalContent: '' })),
                exploracionAuditiva: Array(6).fill(null).map(() => ({ content: '', editing: false, originalContent: '' })),
                desarrolloMotor: Array(4).fill(null).map(() => ({ content: '', editing: false, originalContent: '' })),
                habilidadesSocioemocionales: Array(3).fill(null).map(() => ({ content: '', editing: false, originalContent: '' }))
            },

            biblioteca: {
                canciones: [],
                cuentos: [],
                sonidosdelmundo: [],
                favoritos: []
            }
        };
    },

    computed: {
        periodConfig() {
            const configByPeriod = {
                Diario: {
                    skillFactor: 0.6,
                    skillBonus: -8,
                    usageJuegosBias: 8,
                    usageBibliotecaBias: -8,
                    hoursFactor: 0.35
                },
                Semanal: {
                    skillFactor: 1,
                    skillBonus: 0,
                    usageJuegosBias: 0,
                    usageBibliotecaBias: 0,
                    hoursFactor: 1
                },
                Mensual: {
                    skillFactor: 1.15,
                    skillBonus: 4,
                    usageJuegosBias: -6,
                    usageBibliotecaBias: 6,
                    hoursFactor: 3.8
                }
            };

            return configByPeriod[this.statsPeriod] || configByPeriod.Semanal;
        },

        categoryStats() {
            const stats = [
                {
                    key: 'habilidadComunicativa',
                    label: 'Lenguaje',
                    total: this.juegos.habilidadComunicativa.length,
                    count: this.juegos.habilidadComunicativa.filter((item) => item.content).length,
                    fillClass: 'progress-fill--purple'
                },
                {
                    key: 'exploracionAuditiva',
                    label: 'Memoria Auditiva',
                    total: this.juegos.exploracionAuditiva.length,
                    count: this.juegos.exploracionAuditiva.filter((item) => item.content).length,
                    fillClass: 'progress-fill--pink'
                },
                {
                    key: 'desarrolloMotor',
                    label: 'Pensamiento Lógico',
                    total: this.juegos.desarrolloMotor.length,
                    count: this.juegos.desarrolloMotor.filter((item) => item.content).length,
                    fillClass: 'progress-fill--yellow'
                },
                {
                    key: 'habilidadesSocioemocionales',
                    label: 'Atención',
                    total: this.juegos.habilidadesSocioemocionales.length,
                    count: this.juegos.habilidadesSocioemocionales.filter((item) => item.content).length,
                    fillClass: 'progress-fill--green'
                }
            ];

            return stats.map((item) => ({
                ...item,
                percentage: item.total ? Math.round((item.count / item.total) * 100) : 0
            }));
        },

        stimulatedSkills() {
            const favoritesBoost = Math.min(8, this.biblioteca.favoritos.length * 2);
            const config = this.periodConfig;
            const applyPeriod = (baseValue) => {
                const adjustedValue = Math.round((baseValue + favoritesBoost) * config.skillFactor + config.skillBonus);
                return Math.max(5, Math.min(100, adjustedValue));
            };

            return [
                { key: 'lenguaje', label: 'Lenguaje', percentage: applyPeriod(65), fillClass: 'progress-fill--purple' },
                { key: 'memoria', label: 'Memoria Auditiva', percentage: applyPeriod(58), fillClass: 'progress-fill--pink' },
                { key: 'logico', label: 'Pensamiento Lógico', percentage: applyPeriod(70), fillClass: 'progress-fill--yellow' },
                { key: 'atencion', label: 'Atención', percentage: applyPeriod(92), fillClass: 'progress-fill--green' }
            ];
        },

        overallProgress() {
            if (!this.stimulatedSkills.length) return 0;
            const total = this.stimulatedSkills.reduce((sum, skill) => sum + skill.percentage, 0);
            return Math.round(total / this.stimulatedSkills.length);
        },

        strongestSkill() {
            if (!this.stimulatedSkills.length) {
                return { label: 'Sin datos', percentage: 0 };
            }
            return [...this.stimulatedSkills].sort((a, b) => b.percentage - a.percentage)[0];
        },

        weakestSkill() {
            if (!this.stimulatedSkills.length) {
                return { label: 'Sin datos', percentage: 0 };
            }
            return [...this.stimulatedSkills].sort((a, b) => a.percentage - b.percentage)[0];
        },

        totalActivitiesCount() {
            return this.juegos.habilidadComunicativa.length
                + this.juegos.exploracionAuditiva.length
                + this.juegos.desarrolloMotor.length
                + this.juegos.habilidadesSocioemocionales.length;
        },

        completedActivitiesCount() {
            return this.juegos.habilidadComunicativa.filter((item) => item.content).length
                + this.juegos.exploracionAuditiva.filter((item) => item.content).length
                + this.juegos.desarrolloMotor.filter((item) => item.content).length
                + this.juegos.habilidadesSocioemocionales.filter((item) => item.content).length;
        },

        usageBreakdown() {
            const config = this.periodConfig;
            const juegosUsage = this.completedActivitiesCount;
            const bibliotecaUsage = this.biblioteca.canciones.length + this.biblioteca.cuentos.length + this.biblioteca.sonidosdelmundo.length;
            const totalUsage = Math.max(1, juegosUsage + bibliotecaUsage);

            let juegosPercentage = Math.round((juegosUsage / totalUsage) * 100) + config.usageJuegosBias;
            juegosPercentage = Math.max(5, Math.min(95, juegosPercentage));
            let bibliotecaPercentage = 100 - juegosPercentage;
            bibliotecaPercentage = Math.max(5, Math.min(95, bibliotecaPercentage + config.usageBibliotecaBias));

            const normalizedTotal = juegosPercentage + bibliotecaPercentage;

            return {
                juegos: Math.round((juegosPercentage / normalizedTotal) * 100),
                biblioteca: Math.round((bibliotecaPercentage / normalizedTotal) * 100)
            };
        },

        estimatedUsageHours() {
            const config = this.periodConfig;
            const minutes = (this.completedActivitiesCount * 18)
                + ((this.biblioteca.canciones.length + this.biblioteca.cuentos.length + this.biblioteca.sonidosdelmundo.length) * 12)
                + (this.biblioteca.favoritos.length * 8);
            return Math.max(1, Math.round((minutes / 60) * config.hoursFactor));
        },

        topStrengths() {
            const descriptions = {
                'Lenguaje': 'Comunicación verbal y comprensión.',
                'Memoria Auditiva': 'Reconocimiento y retención de sonidos.',
                'Pensamiento Lógico': 'Resolución y secuenciación.',
                'Atención': 'Concentración sostenida en actividades.'
            };

            return [...this.stimulatedSkills]
                .sort((a, b) => b.percentage - a.percentage)
                .slice(0, 2)
                .map((item) => ({
                    ...item,
                    description: descriptions[item.label] || 'Habilidad destacada.'
                }));
        },

        topWeaknesses() {
            const descriptions = {
                'Lenguaje': 'Se recomienda reforzar ejercicios de expresión.',
                'Memoria Auditiva': 'Conviene practicar actividades de repetición sonora.',
                'Pensamiento Lógico': 'Se sugiere aumentar retos de secuencias.',
                'Atención': 'Aplicar sesiones cortas con descansos.'
            };

            return [...this.stimulatedSkills]
                .sort((a, b) => a.percentage - b.percentage)
                .slice(0, 2)
                .map((item) => ({
                    ...item,
                    description: descriptions[item.label] || 'Área con oportunidad de mejora.'
                }));
        },

        historyResults() {
            const ranked = [...this.stimulatedSkills].sort((a, b) => b.percentage - a.percentage);
            return [
                {
                    id: 'r1',
                    level: 'Nivel 2',
                    title: ranked[0] ? ranked[0].label : 'Habilidad Comunicativa',
                    score: `${Math.round(((ranked[0]?.percentage || 75) / 100) * 12)}/12`,
                    percentage: ranked[0]?.percentage || 75,
                    fillClass: 'progress-fill--pink'
                },
                {
                    id: 'r2',
                    level: 'Nivel 1',
                    title: ranked[1] ? ranked[1].label : 'Exploración Auditiva',
                    score: `${Math.round(((ranked[1]?.percentage || 60) / 100) * 12)}/12`,
                    percentage: ranked[1]?.percentage || 60,
                    fillClass: 'progress-fill--purple'
                },
                {
                    id: 'r3',
                    level: 'Nivel 1',
                    title: ranked[2] ? ranked[2].label : 'Desarrollo Motor',
                    score: `${Math.round(((ranked[2]?.percentage || 45) / 100) * 12)}/12`,
                    percentage: ranked[2]?.percentage || 45,
                    fillClass: 'progress-fill--yellow'
                }
            ];
        },

        recentActivity() {
            if (this.biblioteca.favoritos.length) {
                return this.biblioteca.favoritos.slice(0, 3).map((item, index) => ({
                    id: item.id || `fav-${index}`,
                    title: item.content,
                    subtitle: 'Guardado en favoritos'
                }));
            }

            const fallback = [
                { id: 'hc', title: 'Habilidad Comunicativa', subtitle: 'Categoría activa de juegos' },
                { id: 'ea', title: 'Exploración Auditiva', subtitle: 'Categoría activa de juegos' },
                { id: 'bm', title: 'Biblioteca Multimedia', subtitle: 'Recursos listos para explorar' }
            ];

            return fallback;
        }
    },

    methods: {
        handleNaviClick() {
            // Activar animacion de "hablando"
            this.isTalking = true;

            // Mensajes de respuesta aleatorios
            const messages = [
                'Hola, estoy aqui para ayudarte a aprender.',
                'Quieres explorar alguna actividad?',
                'Me encanta aprender contigo.',
                'En que te puedo ayudar hoy?',
                'Vamos a divertirnos aprendiendo.',
                'Puedes explorar juegos, canciones y mucho mas.',
                'Estoy listo para ayudarte.'
            ];

            // Cambiar mensaje aleatoriamente
            this.naviMessage = messages[Math.floor(Math.random() * messages.length)];

            // Desactivar animacion despues de 3 segundos
            setTimeout(() => {
                this.isTalking = false;
                this.naviMessage = 'Hola, en que puedo ayudarte hoy?';
            }, 3000);
        },

        changeSection(section) {
            this.currentSection = section;
            this.activeCategory = null;
        },

        // Metodos para modal de biblioteca
        openLibraryModal(category) {
            this.currentLibraryCategory = category;
            this.showLibraryModal = true;
            this.syncModalScrollLock();
        },

        closeLibraryModal() {
            this.showLibraryModal = false;
            this.currentLibraryCategory = null;
            this.syncModalScrollLock();
        },

        openStatsModal(sectionKey) {
            this.currentStatsView = sectionKey;
            this.showStatsModal = true;
            this.syncModalScrollLock();
        },

        closeStatsModal() {
            this.showStatsModal = false;
            this.currentStatsView = null;
            this.syncModalScrollLock();
        },

        getStatsModalTitle() {
            const titles = {
                estadisticas: 'Estadísticas',
                tiempo: 'Tiempo de uso',
                fortalezas: 'Fortalezas',
                debilidades: 'Debilidades',
                historial: 'Historial'
            };
            return titles[this.currentStatsView] || 'Estadísticas';
        },

        getStatsModalSubtitle() {
            const subtitles = {
                estadisticas: 'Analicemos el rendimiento',
                tiempo: 'Revisemos el tiempo de uso',
                fortalezas: 'Mis fortalezas',
                debilidades: 'Mis debilidades',
                historial: 'Actividades recientes'
            };
            return subtitles[this.currentStatsView] || 'Resumen general';
        },

        openEditProfileModal() {
            this.showEditProfileModal = true;
            this.syncModalScrollLock();
        },

        closeEditProfileModal() {
            this.showEditProfileModal = false;
            this.syncModalScrollLock();
        },

        syncModalScrollLock() {
            const isAnyModalOpen = this.showModal || this.showCategoryModal || this.showLibraryModal || this.showStatsModal || this.showEditProfileModal;
            document.body.classList.toggle('modal-open', isAnyModalOpen);
        },

        getLibraryTitle() {
            const titles = {
                'canciones': 'Canciones',
                'cuentos': 'Cuentos',
                'sonidosdelmundo': 'Sonidos del Mundo',
                'favoritos': 'Favoritos'
            };
            return titles[this.currentLibraryCategory] || '';
        },

        getLibraryDescription() {
            const descriptions = {
                'canciones': 'Explora canciones interactivas para aprender y divertirte',
                'cuentos': 'Descubre historias magicas y educativas',
                'sonidosdelmundo': 'Escucha y aprende sonidos del entorno',
                'favoritos': 'Tus elementos favoritos guardados'
            };
            return descriptions[this.currentLibraryCategory] || '';
        },

        getLibraryIcon() {
            const icons = {
                'canciones': 'icon-canciones',
                'cuentos': 'icon-cuentos',
                'sonidosdelmundo': 'icon-sonidos',
                'favoritos': 'icon-favoritos'
            };
            return icons[this.currentLibraryCategory] || '';
        },

        getLibraryItems() {
            if (!this.currentLibraryCategory) return [];
            return this.biblioteca[this.currentLibraryCategory] || [];
        },

        toggleCategory(categoryKey) {
            this.activeCategory = this.activeCategory === categoryKey ? null : categoryKey;
        },

        openCategoryModal(category) {
            this.currentCategory = category;
            this.showCategoryModal = true;
            this.syncModalScrollLock();
        },

        closeCategoryModal() {
            this.showCategoryModal = false;
            this.currentCategory = null;
            this.syncModalScrollLock();
        },

        getCategoryTitle() {
            const titles = {
                'habilidadComunicativa': 'Habilidad Comunicativa',
                'exploracionAuditiva': 'Exploracion Auditiva',
                'desarrolloMotor': 'Desarrollo Motor',
                'habilidadesSocioemocionales': 'Habilidades Socioemocionales'
            };
            return titles[this.currentCategory] || '';
        },

        getCategoryDescription() {
            const descriptions = {
                'habilidadComunicativa': 'Mejora tu expresion y comprension',
                'exploracionAuditiva': 'Reconoce y discrimina sonidos',
                'desarrolloMotor': 'Mejora tu orientacion espacial',
                'habilidadesSocioemocionales': 'Desarrolla empatia y habilidades sociales'
            };
            return descriptions[this.currentCategory] || '';
        },

        getCategoryIcon() {
            const icons = {
                'habilidadComunicativa': 'icon-comunicativa text-purple-800',
                'exploracionAuditiva': 'icon-auditiva text-pink-800',
                'desarrolloMotor': 'icon-motor text-yellow-800',
                'habilidadesSocioemocionales': 'icon-socioemocional text-teal-800'
            };
            return icons[this.currentCategory] || '';
        },

        getCategoryGames() {
            return this.juegos[this.currentCategory] || [];
        },

        getCategoryGameDescription() {
            const descriptions = {
                'habilidadComunicativa': 'Actividad para fomentar la expresion verbal y comunicacion efectiva.',
                'exploracionAuditiva': 'Actividad para mejorar la identificacion y discriminacion sonora.',
                'desarrolloMotor': 'Actividad para mejorar la orientacion espacial y coordinacion.',
                'habilidadesSocioemocionales': 'Actividad para desarrollar inteligencia emocional y habilidades sociales.'
            };
            return descriptions[this.currentCategory] || '';
        },

        getCategoryModalClass() {
            const classes = {
                'habilidadComunicativa': 'categoria-comunicativa',
                'exploracionAuditiva': 'categoria-auditiva',
                'desarrolloMotor': 'categoria-motor',
                'habilidadesSocioemocionales': 'categoria-socioemocional'
            };
            return classes[this.currentCategory] || 'bg-white';
        },

        getLibraryModalClass() {
            const classes = {
                'canciones': 'categoria-canciones',
                'cuentos': 'categoria-cuentos',
                'sonidosdelmundo': 'categoria-sonidos',
                'favoritos': 'categoria-favoritos'
            };
            return classes[this.currentLibraryCategory] || 'bg-white';
        },

        openGameModal(gameTitle, description) {
            if (!gameTitle) return;
            this.selectedGame = gameTitle;
            this.gameDescription = description;
            this.showModal = true;
            this.showCategoryModal = false;
            this.syncModalScrollLock();
        },

        closeGameModal() {
            this.showModal = false;
            this.selectedGame = '';
            this.gameDescription = '';
            this.syncModalScrollLock();
        },

        toggleFavorite(item) {
            item.isFavorite = !item.isFavorite;

            if (item.isFavorite) {
                this.biblioteca.favoritos.push(item);
                this.toastMessage = `"${item.content}" ha sido guardado a tus Favoritos.`;
            } else {
                const index = this.biblioteca.favoritos.findIndex((fav) => fav.id === item.id);
                if (index !== -1) {
                    this.biblioteca.favoritos.splice(index, 1);
                }
                this.toastMessage = `"${item.content}" ha sido eliminado de tus Favoritos.`;
            }

            this.showToast = true;
            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        },

        getIconClass(item) {
            const normalize = (str) => (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const content = normalize(item.content);

            // Iconos de juegos - Habilidad Comunicativa
            if (content.includes('sonoros')) return 'icon-sonido';
            if (content.includes('tactil')) return 'icon-tactil';
            if (content.includes('navi') || content.includes('conversacion')) return 'icon-robot';
            if (content.includes('rimas')) return 'icon-ritmo';
            if (content.includes('dictado')) return 'icon-escritura';
            if (content.includes('lectura')) return 'icon-libro';
            if (content.includes('karaoke')) return 'icon-karaoke';
            if (content.includes('preguntas')) return 'icon-preguntas';

            // Iconos de juegos - Exploracion Auditiva
            if (content.includes('ambiental')) return 'icon-ambiental';
            if (content.includes('secuencia')) return 'icon-secuencia';
            if (content.includes('asociacion')) return 'icon-asociacion';
            if (content.includes('espacial')) return 'icon-espacial';
            if (content.includes('persecusion') || content.includes('ritmo')) return 'icon-persecusion';
            if (content.includes('instrumentos')) return 'icon-instrumentos';

            // Iconos de juegos - Desarrollo Motor
            if (content.includes('seguimiento')) return 'icon-seguimiento';
            if (content.includes('cubos')) return 'icon-cubos';
            if (content.includes('bloques') || content.includes('braille')) return 'icon-bloques';
            if (content.includes('descubriendo') || content.includes('medio')) return 'icon-descubriendo';

            // Iconos de juegos - Habilidades Socioemocionales
            if (content.includes('diario')) return 'icon-diario';
            if (content.includes('emocional')) return 'icon-emocional';
            if (content.includes('equipo')) return 'icon-equipo';

            // Iconos de biblioteca - Canciones
            if (content.includes('unodos') || content.includes('uno')) return 'icon-unodos';
            if (content.includes('abc')) return 'icon-abc';
            if (content.includes('cabeza') || content.includes('hombros')) return 'icon-cabeza';
            if (content.includes('vibora')) return 'icon-vibora';
            if (content.includes('trenecito') || content.includes('tren')) return 'icon-trenecito';
            if (content.includes('feliz')) return 'icon-feliz';
            if (content.includes('enojo')) return 'icon-enojo';
            if (content.includes('manita') || content.includes('mano')) return 'icon-manita';

            // Iconos de biblioteca - Cuentos
            if (content.includes('cerditos') || content.includes('cerdo')) return 'icon-cerditos';
            if (content.includes('gallinita') || content.includes('gallina')) return 'icon-gallinita';
            if (content.includes('liebre') || content.includes('tortuga')) return 'icon-liebre';
            if (content.includes('leon') || content.includes('raton')) return 'icon-raton';

            // Iconos de biblioteca - Sonidos del Mundo
            if (content.includes('animales')) return 'icon-animales';
            if (content.includes('transporte')) return 'icon-transporte';
            if (content.includes('objetos') || content.includes('cotidiano')) return 'icon-objetos';
            if (content.includes('natural') || content.includes('naturaleza')) return 'icon-natural';

            // Default - usar icono de categoria segun el contexto
            return 'icon-default';
        },

        initializeLibraryData(contentArray, categoryKey) {
            return contentArray.map((content, index) => ({
                id: `${categoryKey}-${index}`,
                content,
                category: categoryKey,
                editing: false,
                originalContent: content,
                isFavorite: false
            }));
        },

        setThemePreference(theme) {
            this.themePreference = theme;
            localStorage.setItem('navi-theme-preference', theme);
            this.applyThemePreference();
        },

        toggleThemeQuick() {
            const nextTheme = this.isDarkTheme ? 'light' : 'dark';
            this.setThemePreference(nextTheme);
        },

        applyThemePreference() {
            const prefersDark = this.systemThemeMediaQuery ? this.systemThemeMediaQuery.matches : false;
            const shouldUseDark = this.themePreference === 'dark' || (this.themePreference === 'system' && prefersDark);
            document.body.classList.toggle('theme-dark', shouldUseDark);
            document.documentElement.classList.toggle('theme-dark', shouldUseDark);
            this.isDarkTheme = shouldUseDark;
        },

        handleSystemThemeChange() {
            if (this.themePreference === 'system') {
                this.applyThemePreference();
            }
        },

        getAudioMultiplier() {
            const clamp = (value) => Math.max(0, Math.min(1, value / 100));
            const master = clamp(this.audioSettings.master);
            return master;
        },

        applyVolumeToMediaElement(mediaElement) {
            if (!(mediaElement instanceof HTMLMediaElement)) {
                return;
            }

            mediaElement.volume = this.getAudioMultiplier();
        },

        applyAudioSettings() {
            const mediaElements = document.querySelectorAll('audio, video');
            mediaElements.forEach((mediaElement) => {
                this.applyVolumeToMediaElement(mediaElement);
            });
        },

        saveAudioSettings() {
            localStorage.setItem('navi-audio-settings', JSON.stringify(this.audioSettings));
        },

        loadAudioSettings() {
            const rawValue = localStorage.getItem('navi-audio-settings');
            if (!rawValue) {
                return;
            }

            try {
                const parsed = JSON.parse(rawValue);
                this.audioSettings.master = Number.isFinite(parsed.master) ? Math.max(0, Math.min(100, parsed.master)) : this.audioSettings.master;
            } catch (error) {
                localStorage.removeItem('navi-audio-settings');
            }
        },

        handleAudioSettingInput() {
            this.saveAudioSettings();
            this.applyAudioSettings();
        },

        handleMediaPlay(event) {
            this.applyVolumeToMediaElement(event.target);
        }
    },

    watch: {
        currentMode(newMode) {
            // Cuando se cambia a modo Navicito, ir a la seccion NAVI
            if (newMode === 'navicito') {
                this.currentSection = 'navi';
            }
        },

        showModal() {
            this.syncModalScrollLock();
        },

        showCategoryModal() {
            this.syncModalScrollLock();
        },

        showLibraryModal() {
            this.syncModalScrollLock();
        },

        showStatsModal() {
            this.syncModalScrollLock();
        },

        showEditProfileModal() {
            this.syncModalScrollLock();
        }
    },

    mounted() {
        this.systemThemeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        const savedThemePreference = localStorage.getItem('navi-theme-preference');
        if (savedThemePreference && ['light', 'dark', 'system'].includes(savedThemePreference)) {
            this.themePreference = savedThemePreference;
        }

        this.applyThemePreference();
        this.loadAudioSettings();
        this.applyAudioSettings();
        document.addEventListener('play', this.handleMediaPlay, true);

        if (this.systemThemeMediaQuery && this.systemThemeMediaQuery.addEventListener) {
            this.systemThemeMediaQuery.addEventListener('change', this.handleSystemThemeChange);
        }

        const params = new URLSearchParams(window.location.search);
        const requestedSection = params.get('section');
        const shouldOpenEditProfile = params.get('edit') === '1';
        const allowedSections = ['navi', 'juegos', 'biblioteca', 'estadisticas', 'configuracion', 'perfil'];

        if (requestedSection && allowedSections.includes(requestedSection)) {
            this.currentMode = 'tutor';
            this.currentSection = requestedSection;
        }

        if (shouldOpenEditProfile) {
            this.currentMode = 'tutor';
            this.currentSection = 'perfil';
            this.showEditProfileModal = true;
        }

        this.syncModalScrollLock();

        // Inicializar juegos
        this.juegos.habilidadComunicativa[0].content = "Juegos Sonoros";
        this.juegos.habilidadComunicativa[1].content = "Exploracion Tactil Sonora";
        this.juegos.habilidadComunicativa[2].content = "Conversacion con Navi";
        this.juegos.habilidadComunicativa[3].content = "Rimas Auditivas";
        this.juegos.habilidadComunicativa[4].content = "Dictado Ritmico";
        this.juegos.habilidadComunicativa[5].content = "Lectura Auditiva";
        this.juegos.habilidadComunicativa[6].content = "Karaoke";
        this.juegos.habilidadComunicativa[7].content = "Preguntas y Respuestas";

        this.juegos.exploracionAuditiva[0].content = "Sonidos Ambientales";
        this.juegos.exploracionAuditiva[1].content = "Secuencias Sonoras";
        this.juegos.exploracionAuditiva[2].content = "Asociacion Sonido-Emocion";
        this.juegos.exploracionAuditiva[3].content = "Sonido Espacial";
        this.juegos.exploracionAuditiva[4].content = "Ritmo y Persecusion";
        this.juegos.exploracionAuditiva[5].content = "Instrumentos Musicales";

        this.juegos.desarrolloMotor[0].content = "Seguimiento Sonoro";
        this.juegos.desarrolloMotor[1].content = "Cubos y Prismas";
        this.juegos.desarrolloMotor[2].content = "Bloques Braille";
        this.juegos.desarrolloMotor[3].content = "Descubriendo el Medio";

        this.juegos.habilidadesSocioemocionales[0].content = "Diario Personal";
        this.juegos.habilidadesSocioemocionales[1].content = "Navi Emocional";
        this.juegos.habilidadesSocioemocionales[2].content = "Actividades de Equipo";

        // Inicializar biblioteca
        this.biblioteca.canciones = this.initializeLibraryData(initialSongs, 'canciones');
        this.biblioteca.cuentos = this.initializeLibraryData(initialStories, 'cuentos');
        this.biblioteca.sonidosdelmundo = this.initializeLibraryData(initialSounds, 'sonidosdelmundo');
    },

    beforeUnmount() {
        if (this.systemThemeMediaQuery && this.systemThemeMediaQuery.removeEventListener) {
            this.systemThemeMediaQuery.removeEventListener('change', this.handleSystemThemeChange);
        }
        document.removeEventListener('play', this.handleMediaPlay, true);
        document.body.classList.remove('modal-open');
    }
}).mount('#app');
