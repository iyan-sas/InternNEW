{{-- resources/views/livewire/analytics/ojt-charts.blade.php --}}
<div
    x-data="ojtCharts({
        // PIE
        labels: @js($labels ?? []),
        data:   @js($counts ?? []),

        // BAR (Cities)
        cityLabels: @js($cityLabels ?? []),
        cityData:   @js($cityCounts ?? []),

        // DONUT (Approved students)
        joined: @js($joinedTotal ?? 0)
    })"
    x-init="init()"
    @charts:update.window="update($event.detail)"  {{-- keep legacy global event support --}}
    class="grid grid-cols-1 md:grid-cols-3 gap-6"
>
    {{-- PIE: Where students are doing OJT --}}
    <div class="p-4 bg-white border border-zinc-200 rounded-xl shadow-sm">
        <h3 class="font-semibold mb-3">Where students are doing OJT</h3>
        <div class="h-[400px] w-full relative">
            <canvas x-ref="pie" class="w-full h-full"></canvas>
        </div>
    </div>

    {{-- BAR: OJT locations by City --}}
    <div class="p-4 bg-white border border-zinc-200 rounded-xl shadow-sm">
        <h3 class="font-semibold mb-3">OJT locations by City</h3>
        <div class="h-[400px] overflow-x-auto">
            <div class="h-full relative"
                 :style="`min-width:${Math.max(((state.cityLabels?.length || 0) * 140), 480)}px`">
                <canvas x-ref="bar" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    {{-- DONUT: Students joined (Approved only) --}}
    <div class="p-4 bg-white border border-zinc-200 rounded-xl shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Students joined</h3>
            <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-medium text-emerald-700 border-emerald-200 bg-emerald-50">
                Approved
            </span>
        </div>
        <div class="h-[400px] relative">
            <canvas x-ref="joinedChart" class="w-full h-full"></canvas>

            {{-- Big number overlay (shows while chart loads) --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <span class="text-3xl font-bold text-zinc-800" x-text="state.joined"></span>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
document.addEventListener('alpine:init', () => {
    // Plugin: center number inside donut
    const CenterText = {
        id: 'centerText',
        afterDatasetsDraw(chart) {
            if (!chart || chart.config.type !== 'doughnut') return;
            const meta = chart.getDatasetMeta(0);
            if (!meta || !meta.data || !meta.data.length) return;
            const { x, y } = meta.data[0];
            const total = (chart.data.datasets?.[0]?.data?.[0] ?? 0);
            const ctx = chart.ctx;
            ctx.save();
            ctx.font = '700 16px system-ui, -apple-system, Segoe UI, Roboto, Arial';
            ctx.fillStyle = '#111827';
            ctx.textAlign = 'center';
            ctx.fillText(`${total}`, x, y + 4);
            ctx.restore();
        }
    };

    const baseColors = ['#3B82F6','#F43F5E','#F59E0B','#10B981','#8B5CF6','#06B6D4','#EA580C','#84CC16','#EC4899','#22C55E'];
    const palette = (n)=>Array.from({length:n},(_,i)=>baseColors[i%baseColors.length]);

    const sizeToParent = (canvas) => {
        if (!canvas || !canvas.parentElement) return;
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        canvas.style.width = rect.width + 'px';
        canvas.style.height = rect.height + 'px';
    };

    Alpine.data('ojtCharts', (initial) => ({
        pieChart:null, barChart:null, joinedMini:null,
        state:{
            labels: initial.labels ?? [],
            data: initial.data ?? [],
            cityLabels: initial.cityLabels ?? [],
            cityData: initial.cityData ?? [],
            joined: Number(initial.joined ?? 0),
        },

        computeStep(vals){
            const arr = Array.isArray(vals) ? vals.map(v => +v || 0) : [];
            const max = Math.max(0, ...arr);
            if (max <= 10)  return 1;
            if (max <= 20)  return 2;
            if (max <= 50)  return 5;
            if (max <= 100) return 10;
            if (max <= 250) return 25;
            if (max <= 500) return 50;
            const pow = Math.pow(10, Math.floor(Math.log10(max)) - 1);
            return Math.max(1, 5 * pow);
        },

        init(){
            if (!this.$refs.pie || !this.$refs.bar || !this.$refs.joinedChart) return;

            sizeToParent(this.$refs.pie);
            sizeToParent(this.$refs.bar);
            sizeToParent(this.$refs.joinedChart);

            const ctxPie    = this.$refs.pie.getContext('2d');
            const ctxBar    = this.$refs.bar.getContext('2d');
            const ctxJoined = this.$refs.joinedChart.getContext('2d');

            const doResize = () => {
                sizeToParent(this.$refs.pie);
                sizeToParent(this.$refs.bar);
                sizeToParent(this.$refs.joinedChart);
                this.pieChart?.resize();
                this.barChart?.resize();
                this.joinedMini?.resize();
            };
            window.addEventListener('resize', () => setTimeout(doResize, 80));
            new ResizeObserver(() => setTimeout(doResize, 50)).observe(this.$el);

            // ===================== PIE (with clickable legend) =====================
            this.pieChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: this.state.labels,
                    datasets: [{
                        data: this.state.data,
                        backgroundColor: palette(this.state.labels.length),
                        borderWidth: 5,
                        borderColor: '#ffffff',
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        // ✅ CLICKABLE LEGEND (dot + label)
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 8,
                                padding: 16,
                                // hide "Unspecified" in legend if you want; keep it if not desired
                                // filter: (item) => item.text !== 'Unspecified',
                                generateLabels: (chart) => {
                                    const d = chart.data;
                                    return d.labels.map((lbl, i) => ({
                                        text: String(lbl).length > 28 ? String(lbl).slice(0, 25) + '…' : String(lbl),
                                        fillStyle: d.datasets[0].backgroundColor[i],
                                        strokeStyle: d.datasets[0].backgroundColor[i],
                                        hidden: !chart.getDataVisibility(i),
                                        index: i
                                    }));
                                }
                            },
                            onClick: (e, item, legend) => {
                                const ci = legend.chart;
                                const i  = item.index;
                                ci.toggleDataVisibility(i);
                                ci.update();
                            }
                        },
                        // Data labels respect hidden slices
                        datalabels: {
                            formatter: (v, ctx) => {
                                const chart = ctx.chart;
                                const index = ctx.dataIndex;
                                if (!chart.getDataVisibility(index)) return '';
                                return v ? `${chart.data.labels[index]}\n${v}` : '';
                            },
                            color: '#ffffff',
                            anchor: 'center',
                            align: 'center',
                            font: { weight: 700, size: 14 },
                            textAlign: 'center',
                            clamp: true
                        }
                    },
                    elements: { arc: { borderWidth: 5, borderColor: '#ffffff' } }
                },
                plugins: [ChartDataLabels]
            });

            // ===================== BAR (horizontal) =====================
            const grad = ctxBar.createLinearGradient(0, 0, 320, 0);
            grad.addColorStop(0, 'rgba(16,185,129,0.85)');
            grad.addColorStop(1, 'rgba(16,185,129,0.45)');

            this.barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: this.state.cityLabels,
                    datasets: [{
                        label: 'Students',
                        data: this.state.cityData,
                        backgroundColor: grad,
                        borderWidth: 0,
                        borderRadius: 8,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: this.computeStep(this.state.cityData),
                                precision: 0,
                                color: '#6B7280'
                            },
                            grid: { drawBorder: false, color: 'rgba(0,0,0,0.06)' }
                        },
                        y: {
                            ticks: { color: '#6B7280', font: { size: 11 } },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            formatter: (v) => v > 0 ? v : '',
                            color: '#111827',
                            anchor: 'end',
                            align: 'right',
                            offset: 4,
                            font: { weight: 600, size: 11 }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });

            // ===================== DONUT (approved students) =====================
            this.joinedMini = new Chart(ctxJoined, {
                type: 'doughnut',
                data: {
                    labels: ['Approved students'],
                    datasets: [{
                        data: [this.state.joined],
                        backgroundColor: ['#14B8A6'],
                        borderWidth: 0,
                        hoverOffset: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        datalabels: { display: false }
                    }
                },
                plugins: [CenterText]
            });

            // Optional manual event (if you dispatch charts:city elsewhere)
            window.addEventListener('charts:city', (e) => {
                const payload = e.detail?.data || {};
                this.update({
                    cityLabels: payload.labels ?? [],
                    cityData: payload.data ?? []
                });
            });
        },

        update(p = {}){
            // PIE
            if (Array.isArray(p.labels) && Array.isArray(p.data)) {
                this.pieChart.data.labels = p.labels;
                this.pieChart.data.datasets[0].data = p.data;
                this.pieChart.data.datasets[0].backgroundColor =
                    (p.labels ?? []).map((_,i)=>['#3B82F6','#F43F5E','#F59E0B','#10B981','#8B5CF6','#06B6D4','#EA580C','#84CC16','#EC4899','#22C55E'][i%10]);
                this.pieChart.update();
            }

            // BAR
            if (Array.isArray(p.cityLabels) && Array.isArray(p.cityData)) {
                this.barChart.data.labels = p.cityLabels;
                this.barChart.data.datasets[0].data = p.cityData;
                this.barChart.options.scales.x.ticks.stepSize = this.computeStep(p.cityData);
                this.barChart.update();
            }

            // DONUT
            if (p.joined !== undefined) {
                this.state.joined = Number(p.joined) || 0;
                this.joinedMini.data.datasets[0].data = [this.state.joined];
                this.joinedMini.update();
            }
        }
    }));
});
</script>
@endpush
@endonce
