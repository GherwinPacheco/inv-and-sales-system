<!-- View Return Details Modal -->
<div class="modal fade" id="viewReturnDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewReturnDetailsModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="viewReturnDetails-batchId">...</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">

        <!--Barcode and Product Name-->
        <div class="row">
            <div class="col">
                <small class="form-text text-muted" id="viewReturnDetails-barcodeId">...</small>
                <h5 id="viewReturnDetails-productName">...</h5>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Quantity (Single)</small>
                <p id="viewReturnDetails-quantitySingle">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Quantity (Bundle)</small>
                <p id="viewReturnDetails-quantityBundle">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Expiration Date</small>
                <p id="viewReturnDetails-expirationDate">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Expiration Status</small>
                <p id="viewReturnDetails-expirationStatus">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Supplier</small>
                <p id="viewReturnDetails-supplier">...</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted" id="viewReturnDetails-remarksHeader">Return Remarks</small>
                <p id="viewReturnDetails-remarks">...</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col col-6">
                <small class="form-text text-muted">Added by</small>
                <p id="viewReturnDetails-addedBy">...</p>
            </div>

            <div class="col col-6 returned-col">
                <small class="form-text text-muted">Returned by</small>
                <p id="viewReturnDetails-returnedBy">...</p>
            </div>

            <div class="col col-6">
                <small class="form-text text-muted">Added During</small>
                <p id="viewReturnDetails-addedDuring">...</p>
            </div>

            <div class="col col-6 returned-col">
                <small class="form-text text-muted">Returned During</small>
                <p id="viewReturnDetails-returnedDuring">...</p>
            </div>

            
        </div>


    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    </div>
</div>
</div>
</div>


<!-- Mark as Complete Return Modal -->
<form class="m-3" action="./inv-form-markCompleteReturn.php" method="post">
<div class="modal fade" id="markCompleteReturnModal" tabindex="-1" role="dialog" aria-labelledby="markCompleteReturnModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title">Set as Completed</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
    <input type="hidden" id="markCompleteReturn-returnId" name="returnId">
    <div class="row">
        <div class="col">
            <center>
                <p>Are you sure you want to set the
                <br>return of <b><span id="markCompleteReturn-batchIdText"></span></b> as Completed?
                </p>
            </center>
        </div>
    </div>
        
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Set as Completed</button>
    </div>
</div>
</div>
</div>
</form>




<!-- Cancel Return Modal -->
<form class="m-3" action="./inv-form-cancelReturn.php" method="post">
<div class="modal fade" id="cancelReturnModal" tabindex="-1" role="dialog" aria-labelledby="cancelReturnModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title">Cancel Batch Return</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
    <input type="hidden" id="cancelReturn-returnId" name="returnId">
    <input type="hidden" id="cancelReturn-batchId" name="batchId">
    <div class="row">
        <div class="col">
            <center>
                <p>Are you sure you want to
                <br>cancel the return of <b><span id="cancelReturn-batchIdText"></span></b>?
                </p>
                <small class="form-text text-muted">The batch will be returned to the <b>Product List</b></small>
            </center>
        </div>
    </div>

    <!--Cancel Remarks-->
    <div class="row">
        <div class="col">
            <label for="cancelReturn-cancelRemarks">Return Remarks</label>
            <textarea class="form-control" id="returnBatch-returnRemarks" name="cancelRemarks" maxlength="255" placeholder="Add reason for cancelling the return" required></textarea>
        </div>
    </div>
        
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Cancel Return of Batch</button>
    </div>
</div>
</div>
</div>
</form>