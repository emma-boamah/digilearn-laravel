@props([
    'levelGroups' => null,
    'selectedLevelIds' => [],
    'name' => 'level_ids[]',
    'legacyGradeLevel' => null,
    'idPrefix' => 'lvl_sel_',
    'label' => 'Assigned Level(s) / Level Groups',
    'required' => false,
    'helpText' => 'Select one or more levels. You can select an entire level group or pick individual grades.'
])

@php
    $levelGroups = $levelGroups ?? \App\Models\LevelGroup::with('levels')->orderBy('display_order')->get();
    
    // Normalize selected level IDs to array of integers
    if ($selectedLevelIds instanceof \Illuminate\Support\Collection) {
        $selectedIds = $selectedLevelIds->pluck('id')->map(fn($id) => (int)$id)->toArray();
    } elseif (is_array($selectedLevelIds)) {
        $selectedIds = array_map('intval', array_filter($selectedLevelIds));
    } else {
        $selectedIds = [];
    }

    // If selectedIds is empty but legacyGradeLevel is provided, try to preselect matching level
    if (empty($selectedIds) && !empty($legacyGradeLevel)) {
        foreach ($levelGroups as $g) {
            foreach ($g->levels as $l) {
                if (strcasecmp($l->title, $legacyGradeLevel) === 0 || strcasecmp($l->slug, $legacyGradeLevel) === 0) {
                    $selectedIds[] = (int) $l->id;
                    break 2;
                }
            }
        }
    }
@endphp

<div class="level-selector-component" 
     x-data="{
        selected: {{ json_encode($selectedIds) }},
        primaryTitle: '{{ $legacyGradeLevel ?? '' }}',
        toggleLevel(id, title) {
            id = parseInt(id);
            const index = this.selected.indexOf(id);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(id);
            }
            this.updatePrimaryTitle();
        },
        removeLevel(id) {
            id = parseInt(id);
            const index = this.selected.indexOf(id);
            if (index > -1) {
                this.selected.splice(index, 1);
                this.updatePrimaryTitle();
            }
        },
        toggleGroup(levelIds, groupChecked) {
            levelIds.forEach(rawId => {
                const id = parseInt(rawId);
                const index = this.selected.indexOf(id);
                if (groupChecked) {
                    if (index === -1) this.selected.push(id);
                } else {
                    if (index > -1) this.selected.splice(index, 1);
                }
            });
            this.updatePrimaryTitle();
        },
        isGroupAllSelected(levelIds) {
            if (!levelIds || levelIds.length === 0) return false;
            return levelIds.every(id => this.selected.includes(parseInt(id)));
        },
        isGroupPartiallySelected(levelIds) {
            if (!levelIds || levelIds.length === 0) return false;
            const hasSome = levelIds.some(id => this.selected.includes(parseInt(id)));
            const hasAll = this.isGroupAllSelected(levelIds);
            return hasSome && !hasAll;
        },
        updatePrimaryTitle() {
            if (this.selected.length === 0) {
                this.primaryTitle = '';
                return;
            }
            const firstId = this.selected[0];
            const el = document.querySelector(`[data-level-id='${firstId}']`);
            if (el && el.dataset.levelTitle) {
                this.primaryTitle = el.dataset.levelTitle;
            }
        }
     }">

    @if($label)
        <label class="block text-sm font-semibold text-gray-800 mb-1.5 flex items-center justify-between">
            <span>
                {{ $label }}
                @if($required)
                    <span class="text-rose-500">*</span>
                @endif
            </span>
            <span class="text-xs font-normal text-indigo-600 font-mono" x-show="selected.length > 0">
                <span x-text="selected.length"></span> selected
            </span>
        </label>
    @endif

    @if($helpText)
        <p class="text-xs text-gray-500 mb-2.5">{{ $helpText }}</p>
    @endif

    <!-- Hidden fallback for legacy grade_level string column -->
    <input type="hidden" name="grade_level" :value="primaryTitle">

    <!-- Active Pills/Badges Container -->
    <div class="flex flex-wrap gap-1.5 mb-3 p-2 bg-slate-50 border border-slate-200 rounded-lg min-h-[42px] items-center"
         x-show="selected.length > 0">
        <template x-for="id in selected" :key="id">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-sm transition hover:bg-indigo-200">
                <i class="fas fa-layer-group text-[10px] text-indigo-600"></i>
                <span x-text="document.querySelector(`[data-level-id='${id}']`)?.dataset?.levelTitle || ('Level #' + id)"></span>
                <button type="button" @click="removeLevel(id)" class="text-indigo-400 hover:text-indigo-700 focus:outline-none ml-0.5">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </span>
        </template>
    </div>

    <!-- Groups Accordion / Grid -->
    <div class="space-y-2 border border-gray-200 rounded-xl p-3 bg-white shadow-sm max-h-[380px] overflow-y-auto">
        @foreach($levelGroups as $group)
            @php
                $groupLevelIds = $group->levels->pluck('id')->map(fn($id) => (int)$id)->toArray();
            @endphp
            <div class="border border-gray-100 rounded-lg overflow-hidden bg-gray-50/60 transition hover:border-indigo-100"
                 x-data="{ expanded: true }">
                
                <!-- Group Header -->
                <div class="flex items-center justify-between px-3 py-2 bg-gray-100/80 border-b border-gray-100 select-none">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" 
                               id="{{ $idPrefix }}grp_{{ $group->id }}"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer transition"
                               :checked="isGroupAllSelected({{ json_encode($groupLevelIds) }})"
                               :indeterminate.prop="isGroupPartiallySelected({{ json_encode($groupLevelIds) }})"
                               @change="toggleGroup({{ json_encode($groupLevelIds) }}, $event.target.checked)">
                        <label for="{{ $idPrefix }}grp_{{ $group->id }}" class="text-xs font-bold uppercase tracking-wider text-gray-700 cursor-pointer">
                            {{ $group->title ?? $group->name }}
                        </label>
                    </div>
                    
                    <button type="button" 
                            @click="expanded = !expanded" 
                            class="text-gray-400 hover:text-gray-700 p-1 text-xs focus:outline-none transition">
                        <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                </div>

                <!-- Group Levels -->
                <div class="p-2.5 grid grid-cols-2 sm:grid-cols-3 gap-2" x-show="expanded" x-collapse>
                    @foreach($group->levels as $level)
                        <label class="flex items-center gap-2 p-2 rounded-md border text-xs font-medium cursor-pointer transition select-none"
                               :class="selected.includes({{ $level->id }}) 
                                   ? 'bg-indigo-50/90 border-indigo-300 text-indigo-900 shadow-xs' 
                                   : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-100/70 hover:border-gray-300'"
                               data-level-id="{{ $level->id }}"
                               data-level-title="{{ $level->title }}">
                            <input type="checkbox" 
                                   name="{{ $name }}" 
                                   value="{{ $level->id }}"
                                   class="h-3.5 w-3.5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
                                   :checked="selected.includes({{ $level->id }})"
                                   @change="toggleLevel({{ $level->id }}, '{{ addslashes($level->title) }}')">
                            <span class="truncate">{{ $level->title }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
