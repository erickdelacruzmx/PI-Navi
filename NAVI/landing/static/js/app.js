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
            toastVariant: 'info',
            toastTimeoutId: null,
            naviConversation: [],
            naviConversationId: null,
            naviInput: '',
            naviLoading: false,
            naviLoaded: false,
            naviError: '',
            naviListening: false,
            naviSpeaking: false,
            naviStatusMessage: 'Toca el circulo para hablar con Navi.',
            naviVoiceSupported: false,
            naviVoiceOutputSupported: false,
            naviVoiceOutputEnabled: true,
            naviRecognition: null,
            naviSpeechLanguage: 'es-MX',
            naviSpeechRate: 0.95,
            naviSpeechPitch: 1.0,
            naviVoiceProfile: 'suave',
            naviAudioElement: null,
            naviServerPlaybackRate: 1.16,
            naviServerTtsRequestTimeoutMs: 10000,
            naviMaxSpeechChars: 190,
            naviChunkPauseMs: 45,
            naviLastVoiceInputAt: null,
            naviLatencyHistory: [],
            naviAudioOnlyMode: true,
            naviOnboardingCompleted: false,
            naviOnboardingRunning: false,
            isModeTransitioning: false,
            modeTransitionTimeoutId: null,
            modeTransitionDurationMs: 260,
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
        normalizeVoiceCommand(text) {
            return (text || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
        },

        buildNaviSpeechText(text, maxCharsOverride = null) {
            const cleanText = String(text || '').replace(/\s+/g, ' ').trim();
            if (!cleanText) {
                return '';
            }

            const requestedMax = Number(maxCharsOverride);
            const defaultMax = Number(this.naviMaxSpeechChars) || 320;
            const maxChars = Number.isFinite(requestedMax)
                ? Math.max(80, requestedMax)
                : Math.max(80, defaultMax);
            if (cleanText.length <= maxChars) {
                return cleanText;
            }

            const trimmed = cleanText.slice(0, maxChars);
            const breakpoints = ['. ', '? ', '! ', '; '];
            let cutIndex = -1;
            breakpoints.forEach((separator) => {
                const index = trimmed.lastIndexOf(separator);
                if (index > cutIndex) {
                    cutIndex = index;
                }
            });

            const cutThreshold = Math.max(60, Math.floor(maxChars * 0.7));
            const clipped = cutIndex >= cutThreshold
                ? trimmed.slice(0, cutIndex + 1).trim()
                : `${trimmed.trim()}...`;

            return clipped;
        },

        applyNaviProsody(text, { isLastChunk = false } = {}) {
            let normalized = String(text || '').replace(/\s+/g, ' ').trim();
            if (!normalized) {
                return '';
            }

            normalized = normalized
                .replace(/\s+([,.;:!?])/g, '$1')
                .replace(/([,.;:!?])(\S)/g, '$1 $2')
                .replace(/\.{4,}/g, '...')
                .trim();

            const hasTerminal = /[.!?]$/.test(normalized);
            if (!hasTerminal) {
                normalized += isLastChunk ? '.' : ',';
            } else if (!isLastChunk && /[.!?]$/.test(normalized)) {
                normalized = normalized.replace(/[.!?]$/, ',');
            }

            return normalized;
        },

        splitNaviSpeechChunks(text, chunkSizeOverride = null) {
            const cleanText = String(text || '').replace(/\s+/g, ' ').trim();
            if (!cleanText) {
                return [];
            }

            const baseSize = Number(chunkSizeOverride);
            const maxChunkChars = Number.isFinite(baseSize)
                ? Math.max(100, baseSize)
                : Math.max(120, Number(this.naviMaxSpeechChars) || 160);

            const sentences = cleanText
                .split(/(?<=[.!?])\s+/)
                .map((item) => item.trim())
                .filter(Boolean);

            if (!sentences.length) {
                return [this.buildNaviSpeechText(cleanText, maxChunkChars)];
            }

            const chunks = [];
            let current = '';

            const pushCurrent = () => {
                const normalized = this.buildNaviSpeechText(current, maxChunkChars);
                if (normalized) {
                    chunks.push(normalized);
                }
                current = '';
            };

            for (const sentence of sentences) {
                const candidate = current ? `${current} ${sentence}` : sentence;
                if (candidate.length <= maxChunkChars) {
                    current = candidate;
                    continue;
                }

                if (current) {
                    pushCurrent();
                }

                if (sentence.length <= maxChunkChars) {
                    current = sentence;
                    continue;
                }

                // Si una sola oracion es muy larga, dividirla por palabras.
                const words = sentence.split(/\s+/).filter(Boolean);
                let wordChunk = '';
                for (const word of words) {
                    const wordCandidate = wordChunk ? `${wordChunk} ${word}` : word;
                    if (wordCandidate.length <= maxChunkChars) {
                        wordChunk = wordCandidate;
                    } else {
                        const normalizedWordChunk = this.buildNaviSpeechText(wordChunk, maxChunkChars);
                        if (normalizedWordChunk) {
                            chunks.push(normalizedWordChunk);
                        }
                        wordChunk = word;
                    }
                }
                if (wordChunk) {
                    const normalizedWordChunk = this.buildNaviSpeechText(wordChunk, maxChunkChars);
                    if (normalizedWordChunk) {
                        chunks.push(normalizedWordChunk);
                    }
                }
            }

            if (current) {
                pushCurrent();
            }

            return chunks.filter(Boolean);
        },

        getNaviTtsTimeoutMs(text) {
            const base = Number(this.naviServerTtsRequestTimeoutMs) || 10000;
            const lengthFactor = Math.min(5000, Math.max(0, String(text || '').length * 20));
            return Math.max(6000, base + lengthFactor);
        },

        sleep(ms) {
            const duration = Math.max(0, Number(ms) || 0);
            return new Promise((resolve) => window.setTimeout(resolve, duration));
        },

        getNaviChunkPauseMs(chunkText = '', isLastChunk = false) {
            if (isLastChunk) {
                return 0;
            }

            const text = String(chunkText || '').trim();
            const base = Math.max(25, Number(this.naviChunkPauseMs) || 70);

            if (!text) {
                return base;
            }

            if (/[:;]$/.test(text)) {
                return base + 18;
            }
            if (/[.!?]$/.test(text)) {
                return base + 14;
            }
            if (/,$/.test(text)) {
                return base + 8;
            }

            return base;
        },

        startNaviLatencySample({ fromVoice = false } = {}) {
            const now = performance.now();
            return {
                id: `navi-${Date.now()}-${Math.floor(Math.random() * 1000)}`,
                fromVoice,
                voiceCapturedAt: fromVoice ? this.naviLastVoiceInputAt : null,
                sendStartedAt: now,
                responseReceivedAt: null,
                ttsStartedAt: null,
                ttsCompletedAt: null,
                chunkCount: 0,
            };
        },

        publishNaviLatencySample(sample, status = 'ok') {
            if (!sample) {
                return;
            }

            const toMs = (value) => (Number.isFinite(value) ? Math.round(value) : null);
            const metrics = {
                id: sample.id,
                status,
                from_voice: sample.fromVoice,
                stt_to_request_ms: sample.voiceCapturedAt ? toMs(sample.sendStartedAt - sample.voiceCapturedAt) : null,
                backend_ms: sample.responseReceivedAt ? toMs(sample.responseReceivedAt - sample.sendStartedAt) : null,
                tts_queue_ms: sample.ttsStartedAt && sample.responseReceivedAt
                    ? toMs(sample.ttsStartedAt - sample.responseReceivedAt)
                    : null,
                tts_play_ms: sample.ttsCompletedAt && sample.ttsStartedAt
                    ? toMs(sample.ttsCompletedAt - sample.ttsStartedAt)
                    : null,
                end_to_end_ms: sample.ttsCompletedAt
                    ? toMs(sample.ttsCompletedAt - (sample.voiceCapturedAt || sample.sendStartedAt))
                    : null,
                chunk_count: sample.chunkCount || 0,
                timestamp: new Date().toISOString(),
            };

            this.naviLatencyHistory.unshift(metrics);
            if (this.naviLatencyHistory.length > 20) {
                this.naviLatencyHistory.length = 20;
            }

            console.info('[NaviLatency]', metrics);
        },

        showAppToast(message, variant = 'info', durationMs = 3200) {
            if (!message) {
                return;
            }

            this.toastMessage = message;
            this.toastVariant = variant;
            this.showToast = true;

            if (this.toastTimeoutId) {
                window.clearTimeout(this.toastTimeoutId);
            }

            this.toastTimeoutId = window.setTimeout(() => {
                this.showToast = false;
                this.toastTimeoutId = null;
            }, durationMs);
        },

        async saveNaviVoicePreferences(partialPreferences) {
            try {
                await fetch('/api/navi/preferences/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRFToken': this.getCsrfToken(),
                    },
                    body: JSON.stringify(partialPreferences),
                });
            } catch (error) {
                // Evita interrumpir la experiencia de voz por errores de red transitorios.
                console.error('No se pudieron guardar preferencias de Navi.', error);
            }
        },

        applyNaviVoicePreferences(preferences) {
            if (!preferences || typeof preferences !== 'object') {
                return;
            }

            if (typeof preferences.voice_output_enabled === 'boolean') {
                this.naviVoiceOutputEnabled = preferences.voice_output_enabled;
            }

            if (typeof preferences.audio_only_mode === 'boolean') {
                this.naviAudioOnlyMode = preferences.audio_only_mode;
            }

            const parsedRate = Number(preferences.speech_rate);
            if (Number.isFinite(parsedRate)) {
                this.naviSpeechRate = Math.max(0.6, Math.min(1.4, parsedRate));
            }

            const parsedPitch = Number(preferences.speech_pitch);
            if (Number.isFinite(parsedPitch)) {
                this.naviSpeechPitch = Math.max(0.6, Math.min(1.4, parsedPitch));
            }

            if (typeof preferences.speech_lang === 'string' && preferences.speech_lang.trim()) {
                this.naviSpeechLanguage = preferences.speech_lang.trim();
                if (this.naviRecognition) {
                    this.naviRecognition.lang = this.naviSpeechLanguage;
                }
            }

            if (typeof preferences.voice_profile === 'string' && ['suave', 'clara'].includes(preferences.voice_profile)) {
                this.naviVoiceProfile = 'suave';
            }

            if (typeof preferences.onboarding_completed === 'boolean') {
                this.naviOnboardingCompleted = preferences.onboarding_completed;
            }
        },

        playNaviOnboardingTutorial(force = false) {
            if (this.naviOnboardingRunning) {
                return;
            }
            if (!force && this.naviOnboardingCompleted) {
                return;
            }

            this.naviOnboardingRunning = true;

            const tutorialText = [
                'Hola, soy Navi. Bienvenida o bienvenido.',
                'Este espacio esta pensado para familias y menores con discapacidad visual.',
                'Para hablar conmigo, toca el circulo y espera el mensaje te escucho.',
                'Si necesitas detener la escucha, vuelve a tocar el circulo.',
                'Puedes decir comandos como: repetir, detener, hablar mas lento, hablar mas rapido, o modo solo audio.',
                'Tambien puedes decir: ir a juegos, ir a biblioteca, ir a estadisticas, configuracion o perfil.',
                'Cuando quieras, toca el circulo para comenzar.'
            ].join(' ');

            this.naviConversation.push({
                localId: `tutorial-${Date.now()}`,
                role: 'assistant',
                content: tutorialText,
            });
            this.scrollNaviToBottom();
            this.speakNaviText(tutorialText);

            this.naviOnboardingCompleted = true;
            this.saveNaviVoicePreferences({ onboarding_completed: true });
            this.naviStatusMessage = 'Tutorial reproducido. Toca el circulo para hablar con Navi.';

            window.setTimeout(() => {
                this.naviOnboardingRunning = false;
            }, 1200);
        },

        handleNaviVoiceCommand(rawTranscript) {
            const command = this.normalizeVoiceCommand(rawTranscript);
            if (!command) {
                return false;
            }

            if (command.includes('detener') || command.includes('parar') || command.includes('silencio')) {
                this.stopNaviSpeech();
                this.naviStatusMessage = 'Audio detenido. Toca el circulo para seguir.';
                return true;
            }

            if (command.includes('repetir')) {
                const lastAssistantMessage = [...this.naviConversation]
                    .reverse()
                    .find((message) => message.role === 'assistant' && message.content);
                if (lastAssistantMessage) {
                    this.speakNaviText(lastAssistantMessage.content);
                    this.naviStatusMessage = 'Repitiendo la ultima respuesta.';
                } else {
                    this.naviStatusMessage = 'Aun no tengo una respuesta para repetir.';
                }
                return true;
            }

            if (command.includes('tutorial') || command.includes('ayuda')) {
                this.playNaviOnboardingTutorial(true);
                return true;
            }

            if (command.includes('voz suave') || command.includes('voz infantil')) {
                this.setNaviVoiceProfile('suave');
                return true;
            }

            if (command.includes('voz clara') || command.includes('voz tutor')) {
                this.setNaviVoiceProfile('suave');
                this.naviStatusMessage = 'Mantendre voz infantil para Navi.';
                return true;
            }

            if (command.includes('hablar mas lento') || command.includes('mas lento')) {
                this.naviSpeechRate = Math.max(0.6, this.naviSpeechRate - 0.1);
                this.saveNaviVoicePreferences({ speech_rate: this.naviSpeechRate });
                this.naviStatusMessage = `Listo. Nueva velocidad ${this.naviSpeechRate.toFixed(2)}.`;
                return true;
            }

            if (command.includes('hablar mas rapido') || command.includes('mas rapido')) {
                this.naviSpeechRate = Math.min(1.4, this.naviSpeechRate + 0.1);
                this.saveNaviVoicePreferences({ speech_rate: this.naviSpeechRate });
                this.naviStatusMessage = `Listo. Nueva velocidad ${this.naviSpeechRate.toFixed(2)}.`;
                return true;
            }

            if (command.includes('modo solo audio')) {
                const activate = !command.includes('desactivar') && !command.includes('quitar');
                this.naviAudioOnlyMode = activate;
                this.saveNaviVoicePreferences({ audio_only_mode: this.naviAudioOnlyMode });
                this.naviStatusMessage = activate
                    ? 'Modo solo audio activado.'
                    : 'Modo solo audio desactivado.';
                return true;
            }

            const sectionMap = {
                juegos: 'juegos',
                biblioteca: 'biblioteca',
                estadisticas: 'estadisticas',
                configuracion: 'configuracion',
                perfil: 'perfil',
                navi: 'navi',
            };
            for (const key in sectionMap) {
                if (command.includes(`ir a ${key}`) || command === key) {
                    this.currentMode = 'tutor';
                    this.changeSection(sectionMap[key]);
                    this.naviStatusMessage = `Abriendo seccion ${key}.`;
                    return true;
                }
            }

            return false;
        },

        initNaviVoice() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.naviVoiceSupported = Boolean(SpeechRecognition);
            this.naviVoiceOutputSupported = typeof Audio !== 'undefined';

            const savedVoiceOutput = localStorage.getItem('navi-voice-output-enabled');
            if (savedVoiceOutput === '0') {
                this.naviVoiceOutputEnabled = false;
            }

            if (this.naviVoiceSupported) {
                const recognition = new SpeechRecognition();
                recognition.lang = this.naviSpeechLanguage;
                recognition.interimResults = false;
                recognition.continuous = false;
                recognition.maxAlternatives = 1;

                recognition.onstart = () => {
                    this.naviListening = true;
                    this.naviError = '';
                    this.naviStatusMessage = 'Te escucho... habla ahora. Toca el circulo para detener.';
                };

                recognition.onresult = (event) => {
                    const transcript = event.results?.[0]?.[0]?.transcript?.trim() || '';
                    if (!transcript) {
                        this.naviStatusMessage = 'No detecte voz. Toca el circulo para intentar de nuevo.';
                        return;
                    }

                    if (this.handleNaviVoiceCommand(transcript)) {
                        return;
                    }

                    this.naviInput = transcript;
                    this.naviLastVoiceInputAt = performance.now();
                    this.naviStatusMessage = 'Entendido. Estoy procesando tu mensaje...';
                    this.sendNaviMessage(transcript);
                };

                recognition.onerror = (event) => {
                    this.naviListening = false;

                    if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                        this.naviError = 'Debes permitir acceso al microfono para usar Navi por voz.';
                    } else if (event.error === 'no-speech') {
                        this.naviError = 'No detecte voz. Intenta hablar mas cerca del microfono.';
                    } else {
                        this.naviError = 'Ocurrio un problema con el reconocimiento de voz.';
                    }

                    this.showAppToast(this.naviError, 'error');

                    this.naviStatusMessage = 'No pude escucharte. Toca el circulo para reintentar.';
                };

                recognition.onend = () => {
                    this.naviListening = false;
                    if (!this.naviLoading && !this.naviSpeaking) {
                        this.naviStatusMessage = 'Toca el circulo para hablar con Navi.';
                    }
                };

                this.naviRecognition = recognition;
            } else {
                this.naviStatusMessage = 'Este navegador no soporta dictado por voz. Usa un navegador compatible con microfono.';
            }
        },

        toggleNaviVoiceInput() {
            if (!this.naviVoiceSupported || !this.naviRecognition) {
                this.focusNaviInput();
                return;
            }

            if (this.naviLoading) {
                this.naviStatusMessage = 'Espera un momento, estoy generando respuesta.';
                return;
            }

            if (this.naviListening) {
                this.stopNaviVoiceInput();
                return;
            }

            this.startNaviVoiceInput();
        },

        startNaviVoiceInput() {
            if (!this.naviRecognition || this.naviListening) {
                return;
            }

            this.stopNaviSpeech();

            try {
                this.naviRecognition.start();
            } catch (error) {
                this.naviError = 'No fue posible iniciar el microfono en este momento.';
                this.naviStatusMessage = 'Intenta de nuevo en unos segundos.';
                this.showAppToast(this.naviError, 'error');
            }
        },

        stopNaviVoiceInput() {
            if (!this.naviRecognition || !this.naviListening) {
                return;
            }

            try {
                this.naviRecognition.stop();
            } catch (error) {
                this.naviListening = false;
            }
        },

        toggleNaviVoiceOutput() {
            if (!this.naviVoiceOutputSupported) {
                return;
            }

            this.naviVoiceOutputEnabled = !this.naviVoiceOutputEnabled;
            localStorage.setItem('navi-voice-output-enabled', this.naviVoiceOutputEnabled ? '1' : '0');
            this.saveNaviVoicePreferences({ voice_output_enabled: this.naviVoiceOutputEnabled });

            if (!this.naviVoiceOutputEnabled) {
                this.stopNaviSpeech();
                this.naviStatusMessage = 'La salida de voz de Navi esta desactivada.';
            } else {
                this.naviStatusMessage = 'La salida de voz de Navi esta activada.';
            }
        },

        async fetchNaviServerTts(text, timeoutOverrideMs = null) {
            const controller = new AbortController();
            const timeoutMs = Math.max(2500, Number(timeoutOverrideMs) || this.getNaviTtsTimeoutMs(text));
            const timeoutId = window.setTimeout(() => controller.abort(), timeoutMs);

            let response;
            try {
                response = await fetch('/api/navi/tts/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRFToken': this.getCsrfToken(),
                    },
                    body: JSON.stringify({
                        text,
                        voice_profile: 'suave',
                    }),
                    signal: controller.signal,
                });
            } catch (error) {
                if (error && error.name === 'AbortError') {
                    throw new Error('La voz natural esta tardando demasiado.');
                }
                throw error;
            } finally {
                window.clearTimeout(timeoutId);
            }

            const data = await this.parseNaviApiResponse(response, 'No se pudo sintetizar audio.');
            if (!response.ok) {
                throw new Error((data && data.error) || `No se pudo sintetizar audio (HTTP ${response.status}).`);
            }

            if (data.tts_provider !== 'gemini') {
                throw new Error('La respuesta de voz no proviene de Gemini.');
            }

            return data;
        },

        async playNaviServerAudio(text, timeoutOverrideMs = null) {
            const data = await this.fetchNaviServerTts(text, timeoutOverrideMs);
            const mimeType = data.mime_type || 'audio/wav';
            const audioBase64 = data.audio_base64;
            if (!audioBase64) {
                throw new Error('Audio vacio recibido de TTS.');
            }

            const normalizedMimeType = String(mimeType).split(';')[0].trim().toLowerCase();
            const probeAudio = document.createElement('audio');
            if (normalizedMimeType && !probeAudio.canPlayType(normalizedMimeType)) {
                throw new Error(`Formato de audio no compatible en este navegador: ${mimeType}`);
            }

            const audio = new Audio(`data:${mimeType};base64,${audioBase64}`);
            audio.volume = this.getAudioMultiplier();
            audio.playbackRate = Math.max(0.9, Math.min(1.35, Number(this.naviServerPlaybackRate) || 1.0));
            // Desactiva preservacion de pitch para que el tono suba junto con la velocidad.
            try {
                audio.preservesPitch = false;
                audio.mozPreservesPitch = false;
                audio.webkitPreservesPitch = false;
            } catch (e) {
                // Algunos navegadores no soportan estas propiedades.
            }

            this.naviAudioElement = audio;

            await new Promise((resolve, reject) => {
                audio.onplay = () => {
                    this.naviSpeaking = true;
                    this.naviStatusMessage = 'Estoy respondiendo con voz de Gemini.';
                };

                audio.onended = () => {
                    this.naviSpeaking = false;
                    this.naviAudioElement = null;
                    if (!this.naviListening && !this.naviLoading) {
                        this.naviStatusMessage = 'Toca el circulo para hablar con Navi.';
                    }
                    resolve();
                };

                audio.onerror = () => {
                    this.naviSpeaking = false;
                    this.naviAudioElement = null;
                    reject(new Error('No se pudo reproducir el audio TTS.'));
                };

                audio.play().catch((error) => {
                    this.naviSpeaking = false;
                    this.naviAudioElement = null;
                    reject(error instanceof Error ? error : new Error('No se pudo iniciar la reproduccion de audio.'));
                });
            });
        },

        async speakNaviText(text, telemetry = null) {
            if (!this.naviVoiceOutputEnabled || !text) {
                return;
            }

            const speechChunks = this.splitNaviSpeechChunks(text);
            if (!speechChunks.length) {
                return;
            }

            this.stopNaviSpeech();
            this.naviStatusMessage = 'Generando voz de Gemini...';
            let skippedChunks = 0;

            for (let index = 0; index < speechChunks.length; index += 1) {
                const isFirstChunk = index === 0;
                const isLastChunk = index === speechChunks.length - 1;
                const chunk = this.applyNaviProsody(speechChunks[index], { isLastChunk });

                if (telemetry) {
                    telemetry.chunkCount = speechChunks.length;
                }

                if (!isFirstChunk) {
                    this.naviStatusMessage = `Continuando respuesta (${index + 1}/${speechChunks.length})...`;
                }

                let played = false;
                let lastChunkError = null;

                try {
                    await this.playNaviServerAudio(chunk, this.getNaviTtsTimeoutMs(chunk));
                    played = true;
                } catch (error) {
                    lastChunkError = error;
                }

                if (!played) {
                    try {
                        this.naviStatusMessage = 'Ajustando voz para continuar...';
                        await this.playNaviServerAudio(chunk, this.getNaviTtsTimeoutMs(chunk) + 4000);
                        played = true;
                    } catch (retrySameChunkError) {
                        lastChunkError = retrySameChunkError;
                    }
                }

                if (!played) {
                    const errorMessage = String(lastChunkError?.message || '').toLowerCase();
                    const timedOut = errorMessage.includes('tardando demasiado');
                    const quickSpeechText = this.buildNaviSpeechText(chunk, 96);

                    if (timedOut && quickSpeechText && quickSpeechText !== chunk) {
                        try {
                            this.naviStatusMessage = 'Reintentando bloque de voz mas breve...';
                            await this.playNaviServerAudio(quickSpeechText, 9000);
                            played = true;
                        } catch (retryShortError) {
                            lastChunkError = retryShortError;
                        }
                    }
                }

                if (!played) {
                    skippedChunks += 1;
                    const authBlocked = String(lastChunkError?.message || '').toLowerCase().includes('403')
                        || String(lastChunkError?.message || '').toLowerCase().includes('sesion ha expirado')
                        || String(lastChunkError?.message || '').toLowerCase().includes('solicitud bloqueada');
                    if (authBlocked) {
                        this.naviVoiceOutputEnabled = false;
                        this.naviStatusMessage = 'Voz pausada por bloqueo de seguridad. Recarga sesion para continuar.';
                        this.showAppToast('Bloqueo 403 detectado. Recarga sesion y vuelve a intentar.', 'error');
                        if (telemetry) {
                            telemetry.ttsCompletedAt = performance.now();
                            this.publishNaviLatencySample(telemetry, 'auth-blocked');
                        }
                        return;
                    }

                    if (isLastChunk) {
                        this.naviStatusMessage = 'No pude completar la ultima parte de la respuesta por voz.';
                        this.showAppToast(lastChunkError?.message || 'No pude generar audio con Gemini. Intenta de nuevo.', 'warning');
                        if (telemetry) {
                            telemetry.ttsCompletedAt = performance.now();
                            this.publishNaviLatencySample(telemetry, 'tts-partial');
                        }
                        return;
                    }

                    this.naviStatusMessage = `Continuando respuesta (${index + 2}/${speechChunks.length})...`;
                    continue;
                }

                if (telemetry && !telemetry.ttsStartedAt) {
                    telemetry.ttsStartedAt = performance.now();
                }

                if (isLastChunk) {
                    if (!this.naviListening && !this.naviLoading) {
                        this.naviStatusMessage = 'Toca el circulo para hablar con Navi.';
                    }
                } else {
                    await this.sleep(this.getNaviChunkPauseMs(chunk, false));
                }
            }

            if (skippedChunks > 0) {
                this.showAppToast(`Reproduje la mayor parte de la respuesta. Omiti ${skippedChunks} bloque(s) por conectividad.`, 'warning');
            }

            if (telemetry) {
                telemetry.ttsCompletedAt = performance.now();
                this.publishNaviLatencySample(telemetry, skippedChunks > 0 ? 'tts-partial' : 'ok');
            }
        },

        setNaviVoiceProfile(profile) {
            if (!['suave', 'clara'].includes(profile)) {
                return;
            }
            this.naviVoiceProfile = 'suave';
            this.saveNaviVoicePreferences({ voice_profile: 'suave' });
            this.naviStatusMessage = 'Voz infantil activada.';
        },

        toggleNaviAudioOnlyMode() {
            this.naviAudioOnlyMode = !this.naviAudioOnlyMode;
            this.saveNaviVoicePreferences({ audio_only_mode: this.naviAudioOnlyMode });
            this.naviStatusMessage = this.naviAudioOnlyMode
                ? 'Modo solo audio activado.'
                : 'Modo solo audio desactivado.';
        },

        stopNaviSpeech() {
            if (this.naviAudioElement) {
                this.naviAudioElement.pause();
                this.naviAudioElement.currentTime = 0;
                this.naviAudioElement = null;
            }
            this.naviSpeaking = false;
        },

        getCsrfToken() {
            const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            if (metaToken && metaToken !== 'NOTPROVIDED') {
                return metaToken;
            }

            const cookies = document.cookie ? document.cookie.split('; ') : [];
            const csrfCookie = cookies.find((row) => row.startsWith('csrftoken='));
            if (!csrfCookie) {
                return '';
            }
            return decodeURIComponent(csrfCookie.split('=')[1]);
        },

        getNaviHttpErrorMessage(status, fallbackMessage) {
            const httpStatus = Number(status || 0);
            if (httpStatus === 401) {
                return 'Tu sesion ha expirado. Inicia sesion nuevamente.';
            }
            if (httpStatus === 403) {
                return 'Solicitud bloqueada (403). Verifica login activo, CSRF_TRUSTED_ORIGINS y dominio HTTPS en el servidor.';
            }
            if (httpStatus >= 500) {
                return 'Error temporal del servidor. Revisa logs de Nginx/Gunicorn y de Django.';
            }
            return `${fallbackMessage} (HTTP ${httpStatus || 'desconocido'}).`;
        },

        async parseNaviApiResponse(response, fallbackMessage) {
            const status = Number(response?.status || 0);
            const contentType = String(response?.headers?.get('content-type') || '').toLowerCase();
            const bodyText = await response.text();
            const trimmed = String(bodyText || '').trim();

            if (!trimmed) {
                if (!response?.ok) {
                    throw new Error(this.getNaviHttpErrorMessage(status, fallbackMessage));
                }
                return {};
            }

            const looksHtml = contentType.includes('text/html')
                || trimmed.startsWith('<!DOCTYPE html')
                || trimmed.startsWith('<html');

            if (looksHtml) {
                if (response?.url?.includes('/accounts/login')) {
                    throw new Error('Tu sesion ha expirado. Inicia sesion nuevamente.');
                }
                if (status === 403) {
                    throw new Error(this.getNaviHttpErrorMessage(status, fallbackMessage));
                }
                if (status >= 500) {
                    throw new Error(this.getNaviHttpErrorMessage(status, fallbackMessage));
                }
                throw new Error(`${fallbackMessage} El servidor devolvio HTML en lugar de JSON (HTTP ${status}).`);
            }

            try {
                return JSON.parse(trimmed);
            } catch (error) {
                if (!response?.ok) {
                    throw new Error(this.getNaviHttpErrorMessage(status, fallbackMessage));
                }
                throw new Error(`${fallbackMessage} Respuesta no valida del servidor (esperaba JSON).`);
            }
        },

        focusNaviInput() {
            this.$nextTick(() => {
                this.$refs.naviInput?.focus();
            });
        },

        scrollNaviToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.naviMessagesContainer;
                if (!container) return;
                container.scrollTop = container.scrollHeight;
            });
        },

        async loadNaviConversation() {
            this.naviError = '';
            try {
                const response = await fetch('/api/navi/conversation/', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await this.parseNaviApiResponse(response, 'No se pudo cargar la conversacion.');
                if (!response.ok) {
                    throw new Error((data && data.error) || `No se pudo cargar la conversacion (HTTP ${response.status}).`);
                }
                this.naviConversationId = data.conversation_id;
                this.naviConversation = Array.isArray(data.messages) ? data.messages : [];
                this.applyNaviVoicePreferences(data.voice_preferences || {});
                this.naviLoaded = true;
                this.scrollNaviToBottom();

                if (!this.naviOnboardingCompleted) {
                    this.playNaviOnboardingTutorial(false);
                }
            } catch (error) {
                this.naviError = 'No pude cargar tu conversacion. Recarga la pagina para intentarlo de nuevo.';
                this.showAppToast(this.naviError, 'error');
            }
        },

        async sendNaviMessage(forcedMessage = null) {
            const message = (forcedMessage ?? this.naviInput).trim();
            if (!message || this.naviLoading) {
                return;
            }

            const telemetry = this.startNaviLatencySample({ fromVoice: forcedMessage !== null });

            if (this.handleNaviVoiceCommand(message)) {
                this.naviInput = '';
                return;
            }

            this.naviError = '';
            this.naviLoading = true;
            this.naviStatusMessage = 'Estoy pensando tu respuesta...';

            const optimisticUserMessage = {
                localId: `local-${Date.now()}`,
                role: 'user',
                content: message,
            };
            this.naviConversation.push(optimisticUserMessage);
            this.naviInput = '';
            this.scrollNaviToBottom();

            try {
                const response = await fetch('/api/navi/chat/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRFToken': this.getCsrfToken(),
                    },
                    body: JSON.stringify({
                        message,
                        conversation_id: this.naviConversationId,
                    }),
                });

                const data = await this.parseNaviApiResponse(response, 'No se pudo obtener respuesta de Navi.');

                if (!response.ok) {
                    const backendError = data && typeof data.error === 'string' ? data.error : '';
                    throw new Error(backendError || `No se pudo obtener respuesta de Navi (HTTP ${response.status}).`);
                }

                this.naviConversationId = data.conversation_id || this.naviConversationId;
                if (data.message) {
                    this.naviConversation.push(data.message);
                    telemetry.responseReceivedAt = performance.now();
                    this.speakNaviText(data.message.content, telemetry);
                } else {
                    this.publishNaviLatencySample(telemetry, 'no-message');
                }
                this.scrollNaviToBottom();
                if (!this.naviSpeaking) {
                    this.naviStatusMessage = 'Toca el circulo para hablar con Navi.';
                }
            } catch (error) {
                this.naviError = error.message || 'No pude responder en este momento.';
                this.publishNaviLatencySample(telemetry, 'chat-error');
                this.naviConversation.push({
                    localId: `err-${Date.now()}`,
                    role: 'assistant',
                    content: this.naviError,
                });
                this.scrollNaviToBottom();
                this.naviStatusMessage = 'Toca el circulo para intentar de nuevo.';
                this.showAppToast(this.naviError, 'error');
            } finally {
                this.naviLoading = false;
            }
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
                this.showAppToast(`"${item.content}" ha sido guardado a tus Favoritos.`, 'success');
            } else {
                const index = this.biblioteca.favoritos.findIndex((fav) => fav.id === item.id);
                if (index !== -1) {
                    this.biblioteca.favoritos.splice(index, 1);
                }
                this.showAppToast(`"${item.content}" ha sido eliminado de tus Favoritos.`, 'info');
            }
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
            if (this.modeTransitionTimeoutId) {
                window.clearTimeout(this.modeTransitionTimeoutId);
            }
            this.isModeTransitioning = true;
            this.modeTransitionTimeoutId = window.setTimeout(() => {
                this.isModeTransitioning = false;
                this.modeTransitionTimeoutId = null;
            }, this.modeTransitionDurationMs);

            // Cuando se cambia a modo Navicito, ir a la seccion NAVI
            if (newMode === 'navicito') {
                this.currentSection = 'navi';
            }
        },

        currentSection(newSection) {
            if (newSection === 'navi' && !this.naviLoaded) {
                this.loadNaviConversation();
            }

            if (newSection === 'navi') {
                this.naviAudioOnlyMode = true;
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
        document.documentElement.style.setProperty('--mode-transition-duration', `${this.modeTransitionDurationMs}ms`);
        this.systemThemeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        const savedThemePreference = localStorage.getItem('navi-theme-preference');
        if (savedThemePreference && ['light', 'dark', 'system'].includes(savedThemePreference)) {
            this.themePreference = savedThemePreference;
        }

        this.applyThemePreference();
        this.loadAudioSettings();
        this.applyAudioSettings();
        this.initNaviVoice();
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

        if (this.currentSection === 'navi') {
            this.loadNaviConversation();
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
        if (this.toastTimeoutId) {
            window.clearTimeout(this.toastTimeoutId);
            this.toastTimeoutId = null;
        }
        if (this.modeTransitionTimeoutId) {
            window.clearTimeout(this.modeTransitionTimeoutId);
            this.modeTransitionTimeoutId = null;
        }
        this.stopNaviVoiceInput();
        this.stopNaviSpeech();
        if (this.systemThemeMediaQuery && this.systemThemeMediaQuery.removeEventListener) {
            this.systemThemeMediaQuery.removeEventListener('change', this.handleSystemThemeChange);
        }
        document.removeEventListener('play', this.handleMediaPlay, true);
        document.body.classList.remove('modal-open');
    }
}).mount('#app');
