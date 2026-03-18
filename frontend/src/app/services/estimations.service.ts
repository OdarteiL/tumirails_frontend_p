import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import {
  GuestEstimationRequest,
  GuestEstimationResponse,
  Estimation,
  EstimationListResponse,
  EstimationResponse,
  CreateEstimationRequest,
  UpdateEstimationRequest,
  ReverseEstimationRequest,
  ReverseEstimationResponse
} from '../models/estimation.model';

@Injectable({
  providedIn: 'root'
})
export class EstimationsService {
  constructor(private apiService: ApiService) {}

  // Guest estimations
  createGuestEstimation(request: GuestEstimationRequest): Observable<GuestEstimationResponse> {
    return this.apiService.post<GuestEstimationResponse>('/estimations/guest', request);
  }

  getGuestEstimationByCode(code: string): Observable<GuestEstimationResponse> {
    return this.apiService.get<GuestEstimationResponse>(`/estimations/guest/${code}`);
  }

  // Authenticated estimations
  getEstimations(): Observable<EstimationListResponse> {
    return this.apiService.get<EstimationListResponse>('/estimations');
  }

  getEstimation(id: number): Observable<EstimationResponse> {
    return this.apiService.get<EstimationResponse>(`/estimations/${id}`);
  }

  createEstimation(data: CreateEstimationRequest): Observable<EstimationResponse> {
    return this.apiService.post<EstimationResponse>('/estimations', data);
  }

  updateEstimation(id: number, data: UpdateEstimationRequest): Observable<EstimationResponse> {
    return this.apiService.put<EstimationResponse>(`/estimations/${id}`, data);
  }

  deleteEstimation(id: number): Observable<{ success: boolean; message: string }> {
    return this.apiService.delete<{ success: boolean; message: string }>(`/estimations/${id}`);
  }

  // Reverse estimation
  reverseEstimation(data: ReverseEstimationRequest): Observable<ReverseEstimationResponse> {
    return this.apiService.post<ReverseEstimationResponse>('/estimations/reverse', data);
  }
}
