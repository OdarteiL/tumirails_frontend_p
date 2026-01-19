import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { GuestEstimationRequest, GuestEstimationResponse } from '../models/estimation.model';

@Injectable({
    providedIn: 'root'
})
export class EstimationsService {
    constructor(private apiService: ApiService) { }

    createGuestEstimation(request: GuestEstimationRequest): Observable<GuestEstimationResponse> {
        return this.apiService.post<GuestEstimationResponse>('/estimations/guest', request);
    }

    getGuestEstimationByCode(code: string): Observable<GuestEstimationResponse> {
        return this.apiService.get<GuestEstimationResponse>(`/estimations/guest/${code}`);
    }
}
