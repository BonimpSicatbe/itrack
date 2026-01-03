<div>
    <x-modal name="e-sign-modal" :show="$showModal" maxWidth="6xl">
        <div class="bg-white rounded-xl shadow-lg max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center">
                    <i class="fa-solid fa-signature text-white text-xl mr-3"></i>
                    <h3 class="text-lg font-semibold text-white">
                        @if($isResigning)
                            Re-sign Document
                        @else
                            Place Digital Signature
                        @endif
                    </h3>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Page Navigation (only show for multi-page documents) -->
                    @if($showPageSelector)
                    <div class="flex items-center space-x-2 bg-white/20 rounded-lg px-3 py-1">
                        <button wire:click="previousPage" 
                            wire:loading.attr="disabled"
                            class="p-1 text-white hover:bg-white/20 rounded {{ $currentPagePreview <= 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            title="Previous Page">
                            <i class="fa-solid fa-chevron-left text-sm"></i>
                        </button>
                        <span class="text-white text-sm">
                            Page 
                            <select wire:model.live="currentPagePreview" 
                                class="bg-transparent border-none text-white focus:ring-0 focus:border-none text-sm">
                                @for($i = 1; $i <= $totalPages; $i++)
                                    <option value="{{ $i }}" class="text-gray-800">{{ $i }}</option>
                                @endfor
                            </select>
                            of {{ $totalPages }}
                        </span>
                        <button wire:click="nextPage" 
                            wire:loading.attr="disabled"
                            class="p-1 text-white hover:bg-white/20 rounded {{ $currentPagePreview >= $totalPages ? 'opacity-50 cursor-not-allowed' : '' }}"
                            title="Next Page">
                            <i class="fa-solid fa-chevron-right text-sm"></i>
                        </button>
                    </div>
                    @endif
                    
                    <!-- Zoom Controls -->
                    <div class="flex items-center space-x-1 bg-white/20 rounded-lg px-2 py-1">
                        <button wire:click="zoomOut" 
                            class="p-1 text-white hover:bg-white/20 rounded"
                            title="Zoom Out">
                            <i class="fa-solid fa-magnifying-glass-minus text-sm"></i>
                        </button>
                        <span class="text-white text-sm px-2">{{ round($zoomLevel * 100) }}%</span>
                        <button wire:click="zoomIn" 
                            class="p-1 text-white hover:bg-white/20 rounded"
                            title="Zoom In">
                            <i class="fa-solid fa-magnifying-glass-plus text-sm"></i>
                        </button>
                        <button wire:click="resetZoom" 
                            class="p-1 text-white hover:bg-white/20 rounded ml-2"
                            title="Reset Zoom">
                            <i class="fa-solid fa-expand text-sm"></i>
                        </button>
                        <button wire:click="fitToWidth" 
                            class="p-1 text-white hover:bg-white/20 rounded ml-2"
                            title="Fit to Width">
                            <i class="fa-solid fa-arrows-alt-h text-sm"></i>
                        </button>
                    </div>
                    
                    <button wire:click="cancel" 
                        class="text-white hover:text-gray-200 transition-colors p-1">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Left Panel - Tools & Controls -->
                <div class="w-64 border-r border-gray-200 bg-gray-50 p-4 flex flex-col">
                    <!-- Page Selection for Signing -->
                    @if($showPageSelector)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-file-lines mr-1"></i>
                            Select Page to Sign
                        </label>
                        <select wire:model.live="pageNumber" 
                            class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:border-green-600 focus:ring-green-600">
                            @for($i = 1; $i <= $totalPages; $i++)
                                <option value="{{ $i }}">
                                    Page {{ $i }}
                                    @if($i == 1)
                                        (First Page)
                                    @elseif($i == $totalPages)
                                        (Last Page)
                                    @endif
                                </option>
                            @endfor
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Signature will be placed on page {{ $pageNumber }}
                        </p>
                    </div>
                    @else
                    <div class="mb-6 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-700">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Single-page document
                        </p>
                    </div>
                    @endif
                    
                    <!-- Signatory Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-user-pen mr-1"></i>
                            Select Signatory
                        </label>
                        <select wire:model="signatoryId" 
                            class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:border-green-600 focus:ring-green-600">
                            @foreach($signatories as $signatory)
                                <option value="{{ $signatory->id }}">
                                    {{ $signatory->name }} - {{ $signatory->position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- X and Y Position Sliders -->
                    <div class="space-y-4 mb-6 pt-4 border-t border-gray-200">
                        <!-- X Position Slider -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-sm font-medium text-gray-700">
                                    <i class="fa-solid fa-arrows-left-right mr-1"></i>
                                    X Position: {{ round($signatureX) }}px
                                </label>
                                <span class="text-xs text-gray-500">
                                    {{ round($minX) }} - {{ round($maxX) }}px
                                </span>
                            </div>
                            <input type="range" 
                                wire:model.live="signatureX"
                                min="{{ $minX }}" 
                                max="{{ $maxX }}" 
                                step="1"
                                class="w-full h-2 bg-gradient-to-r from-gray-300 via-green-200 to-gray-300 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-green-600">
                        </div>
                        
                        <!-- Y Position Slider -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-sm font-medium text-gray-700">
                                    <i class="fa-solid fa-arrows-up-down mr-1"></i>
                                    Y Position: {{ round($signatureY) }}px
                                </label>
                                <span class="text-xs text-gray-500">
                                    {{ round($minY) }} - {{ round($maxY) }}px
                                </span>
                            </div>
                            <input type="range" 
                                wire:model.live="signatureY"
                                min="{{ $minY }}" 
                                max="{{ $maxY }}" 
                                step="1"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-green-600">
                        </div>
                    </div>

                    <!-- Signature Controls -->
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fa-solid fa-expand-alt mr-1"></i>
                                Size: {{ round($signatureScale * 100) }}%
                            </label>
                            <input type="range" 
                                wire:model.live="signatureScale"
                                min="0.2" 
                                max="2.0" 
                                step="0.1"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-green-600">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fa-solid fa-adjust mr-1"></i>
                                Opacity: {{ round($signatureOpacity * 100) }}%
                            </label>
                            <input type="range" 
                                wire:model.live="signatureOpacity"
                                min="0.3" 
                                max="1.0" 
                                step="0.1"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-green-600">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fa-solid fa-rotate mr-1"></i>
                                Rotation: {{ $signatureRotation }}°
                            </label>
                            <input type="range" 
                                wire:model.live="signatureRotation"
                                min="-45" 
                                max="45" 
                                step="1"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-green-600">
                        </div>
                    </div>

                    <!-- Position Info -->
                    <div class="mt-auto p-3 bg-gray-100 rounded-lg">
                        <p class="text-xs font-medium text-gray-700 mb-1">Document Info:</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="text-gray-600">Page:</div>
                            <div class="text-gray-800">{{ $pageNumber }} of {{ $totalPages }}</div>
                            <div class="text-gray-600">Width:</div>
                            <div class="text-gray-800">{{ round($documentWidth) }}px</div>
                            <div class="text-gray-600">Height:</div>
                            <div class="text-gray-800">{{ round($documentHeight) }}px</div>
                            <div class="text-gray-600">Signature X:</div>
                            <div class="text-gray-800">{{ round($signatureX) }}px</div>
                            <div class="text-gray-600">Signature Y:</div>
                            <div class="text-gray-800">{{ round($signatureY) }}px</div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel - Document Preview -->
                <div class="flex-1 p-4 bg-gray-100 overflow-auto">                    
                    <!-- Instructions -->
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            @if($showPageSelector)
                                You are viewing page {{ $currentPagePreview }}. To sign a different page, select it from the "Select Page to Sign" dropdown.
                            @else
                                Single-page document. Drag the signature to position it, or use the sliders.
                            @endif
                        </p>
                        @if($showPageSelector && $currentPagePreview != $pageNumber)
                        <p class="text-sm text-yellow-600 mt-1">
                            <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                            You are viewing page {{ $currentPagePreview }} but signing page {{ $pageNumber }}.
                        </p>
                        @endif
                    </div>
                    
                    <!-- Document Dimensions Info -->
                    <div class="mb-4 text-center">
                        @if($showPageSelector)
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 mb-2">
                            <i class="fa-solid fa-file-lines mr-2"></i>
                            Signing Page {{ $pageNumber }} of {{ $totalPages }}
                        </div>
                        @endif
                        <div class="text-sm text-gray-600">
                            Document: {{ round($documentWidth / ($zoomLevel > 0 ? $zoomLevel : 1)) }}px × {{ round($documentHeight / ($zoomLevel > 0 ? $zoomLevel : 1)) }}px
                        </div>
                    </div>
                    
                    <div class="relative mx-auto" 
                        style="width: {{ $documentWidth * $zoomLevel }}px; height: {{ $documentHeight * $zoomLevel }}px;">
                        
                        <!-- Document Container -->
                        <div class="absolute top-0 left-0 overflow-hidden bg-white shadow-lg"
                            style="width: {{ $documentWidth * $zoomLevel }}px; height: {{ $documentHeight * $zoomLevel }}px; transform: translate({{ $panX }}px, {{ $panY }}px);">
                            
                            <!-- Document Preview -->
                            @if(in_array($fileExtension, ['pdf']))
                                @php
                                    $iframeWidth = $documentWidth * $zoomLevel;
                                    $iframeHeight = $documentHeight * $zoomLevel;
                                    
                                    // Add page parameter to PDF URL if multi-page
                                    $pdfUrl = $fileUrl;
                                    if ($showPageSelector) {
                                        $pdfUrl .= '#page=' . $currentPagePreview;
                                    }
                                @endphp
                                
                                <iframe src="{{ $fileUrl }}?page={{ $currentPagePreview }}&toolbar=0&navpanes=0&zoom={{ $zoomLevel * 100 }}&view=FitH" 
                                    class="w-full h-full border-0"
                                    style="
                                        width: {{ $iframeWidth }}px;
                                        height: {{ $iframeHeight }}px;
                                    ">
                                </iframe>
                                
                            @elseif(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ $fileUrl }}" 
                                    alt="Document"
                                    class="w-full h-full object-contain"
                                    style="
                                        width: {{ $documentWidth * $zoomLevel }}px;
                                        height: {{ $documentHeight * $zoomLevel }}px;
                                        object-fit: contain;
                                    ">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                    <div class="text-center p-8">
                                        <i class="fa-solid fa-file text-4xl text-gray-400 mb-3"></i>
                                        <p class="text-gray-500">Preview not available for this file type</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Signature Overlay -->
                            @if($selectedSignatory && $currentPagePreview == $pageNumber)
                                @php
                                    $signatureWidth = 100 * $signatureScale;
                                    $signatureHeight = 40 * $signatureScale;
                                    
                                    $signatureMedia = $selectedSignatory->getFirstMedia('signatures');
                                    $signatureUrl = $signatureMedia ? $signatureMedia->getUrl() : null;
                                    
                                    $signatureDataUrl = null;
                                    if ($signatureUrl && file_exists($signatureMedia->getPath())) {
                                        $imagePath = $signatureMedia->getPath();
                                        if (file_exists($imagePath)) {
                                            $imageData = file_get_contents($imagePath);
                                            $imageInfo = getimagesize($imagePath);
                                            $mimeType = $imageInfo['mime'] ?? 'image/png';
                                            $signatureDataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                                        }
                                    }
                                @endphp
                                
                                @if($signatureUrl || $signatureDataUrl)
                                    <div 
                                        wire:click="updateSignaturePosition($event.offsetX, $event.offsetY)"
                                        wire:mousedown="startDrag"
                                        wire:mouseup="stopInteractions"
                                        wire:mouseleave="stopInteractions"
                                        wire:mousemove="if($isDragging) updateSignaturePosition($event.offsetX, $event.offsetY)"
                                        class="absolute cursor-move border-2 border-dashed border-green-500 hover:border-green-600 transition-colors bg-white/50"
                                        style="
                                            left: {{ $signatureX * $zoomLevel }}px;
                                            top: {{ $signatureY * $zoomLevel }}px;
                                            width: {{ $signatureWidth * $zoomLevel }}px;
                                            height: {{ $signatureHeight * $zoomLevel }}px;
                                            opacity: {{ $signatureOpacity }};
                                            transform: rotate({{ $signatureRotation }}deg);
                                        ">
                                        
                                        <!-- Render signature -->
                                        @if($signatureDataUrl)
                                            <img src="{{ $signatureDataUrl }}" 
                                                alt="Signature"
                                                class="w-full h-full object-contain pointer-events-none"
                                                style="filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.2));">
                                        @else
                                            <img src="{{ $signatureUrl }}" 
                                                alt="Signature"
                                                class="w-full h-full object-contain pointer-events-none"
                                                style="filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.2));">
                                        @endif
                                            
                                        <!-- Drag Handle -->
                                        <div class="absolute -top-2 -left-2 w-4 h-4 bg-green-500 rounded-full cursor-move"></div>
                                        <div class="absolute -bottom-2 -right-2 w-4 h-4 bg-green-500 rounded-full cursor-se-resize"
                                            wire:mousedown="startResize"
                                            wire:mouseup="stopInteractions"
                                            wire:mouseleave="stopInteractions"></div>
                                    </div>
                                @else
                                    <div class="absolute border-2 border-dashed border-red-500 bg-red-50 flex items-center justify-center"
                                        style="
                                            left: {{ $signatureX * $zoomLevel }}px;
                                            top: {{ $signatureY * $zoomLevel }}px;
                                            width: {{ $signatureWidth * $zoomLevel }}px;
                                            height: {{ $signatureHeight * $zoomLevel }}px;
                                        ">
                                        <div class="text-red-500 text-xs text-center p-2">
                                            <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                                            <div>Signature image</div>
                                            <div>not available</div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                        
                        <!-- Grid Overlay -->
                        <div class="absolute top-0 left-0 pointer-events-none"
                            style="
                                width: {{ $documentWidth * $zoomLevel }}px;
                                height: {{ $documentHeight * $zoomLevel }}px;
                                background-image: linear-gradient(rgba(0,0,0,0.1) 1px, transparent 1px),
                                                linear-gradient(90deg, rgba(0,0,0,0.1) 1px, transparent 1px);
                                background-size: {{ 20 * $zoomLevel }}px {{ 20 * $zoomLevel }}px;
                                opacity: 0.3;
                            ">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between">
                <div>
                    <button wire:click="togglePreview" 
                        class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-eye mr-1"></i>
                        {{ $showPreview ? 'Hide' : 'Show' }} Preview
                    </button>
                </div>
                
                <div class="flex space-x-3">
                    <button wire:click="cancel" 
                        class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    
                    <button wire:click="applySignature" 
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-green-700 transition-colors flex items-center">
                        @if($isProcessing)
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                            Processing...
                        @else
                            <i class="fa-solid fa-signature mr-2"></i>
                            @if($showPageSelector)
                                Sign Page {{ $pageNumber }} & Approve
                            @else
                                Apply Signature & Approve
                            @endif
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </x-modal>

    <!-- Preview Modal -->
    @if($showPreview && $selectedSignatory)
        @php
            $signatureMedia = $selectedSignatory->getFirstMedia('signatures');
            $signaturePreviewUrl = $signatureMedia ? $signatureMedia->getUrl() : null;
        @endphp
        @if($signaturePreviewUrl)
            <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl p-6 max-w-4xl mx-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Signature Preview</h3>
                        <button wire:click="togglePreview" 
                            class="text-gray-500 hover:text-gray-700">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="border border-gray-300 p-4 bg-white rounded-lg flex items-center justify-center">
                        <img src="{{ $signaturePreviewUrl }}" 
                            alt="Signature Preview"
                            style="
                                opacity: {{ $signatureOpacity }};
                                transform: scale({{ $signatureScale * 2 }}) rotate({{ $signatureRotation }}deg);
                                max-width: 100%;
                                max-height: 400px;
                            ">
                    </div>
                    <div class="mt-4 text-sm text-gray-600 text-center">
                        This is how your signature will appear on the document.
                        @if($showPageSelector)
                            <br><span class="font-semibold">Page: {{ $pageNumber }}</span>
                        @endif
                    </div>
                    <div class="mt-3 text-xs text-gray-500 text-center">
                        <p>Size: {{ round($signatureScale * 100) }}% • 
                           Opacity: {{ round($signatureOpacity * 100) }}% • 
                           Rotation: {{ $signatureRotation }}°</p>
                        <p>Position: ({{ round($signatureX) }}, {{ round($signatureY) }})</p>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>