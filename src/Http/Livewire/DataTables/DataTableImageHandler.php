<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\DataTables;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class DataTableImageHandler extends Component
{
    use WithFileUploads;
    
    public $field;
    public $imageUrl;
    public $recordId;
    public $croppedImage;
    public $showCropModal = false;
    
    protected $listeners = ['openCropImageModalEvent' => 'openCropImageModal'];
    
    /*public function openCropModal($field, $imageUrl, $recordId)
    {
        $this->field = $field;
        $this->imageUrl = $imageUrl;
        $this->recordId = $recordId;
        $this->showCropModal = true;
        
        $this->dispatch('open-crop-modal'); // browser event
    }*/


 public function openCropImageModal($field, $imgUrl, $id)
    {
        
        
        $modalId = "crop-image-modal";
        $data = [
            "modalId" => $modalId,
            "field" => $field,
            "imgUrl" => $imgUrl,
            "id" => $id,
        ];

        //@include('system.views::data-tables.modals.crop-image-modal')
        $modalHtml = view('system.views::data-tables.modals.crop-image-modal', $data)->render();
        //$this->dispatch("open-add-relationship-modal", ['modalHtml' => $modalHtml, "modalId" => $modalId]);
        $data["modalHtml"] = $modalHtml;

        $this->dispatch("show-crop-image-modal-event", $data);
    }




    
    public function cropImage()
    {
        $this->validate([
            'croppedImage' => 'required|image|max:10240', // 10MB max
        ]);
        
        try {
            // Process and store the cropped image
            $image = Image::make($this->croppedImage->getRealPath());
            
            // Crop to desired dimensions (you can make these configurable)
            $image->crop(300, 300);
            
            // Generate filename and path
            $filename = $this->field . '_' . $this->recordId . '_' . time() . '.jpg';
            $path = 'uploads/images/' . $filename;
            
            // Save the image
            Storage::disk('public')->put($path, $image->encode('jpg', 80));
            
            // Emit event to parent component
            $this->dispatch('imageCropped', $this->field, $path);
            
            $this->dispatch('notify', [ // browser event
                'type' => 'success',
                'message' => 'Image cropped and saved successfully.'
            ]);
            
            $this->resetCropModal();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [ // browser event
                'type' => 'error',
                'message' => 'Failed to process image: ' . $e->getMessage()
            ]);
        }
    }
    
    public function resetCropModal()
    {
        $this->reset(['field', 'imageUrl', 'recordId', 'croppedImage', 'showCropModal']);
        $this->dispatch('close-crop-modal'); // browser event
    }
    
    public function render()
    {
        return view('livewire.data-tables.data-table-image-handler');
    }
}